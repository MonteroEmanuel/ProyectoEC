<?php

namespace App\Http\Controllers;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
class ProductoController extends Controller
{
    // Catálogo público: solo productos activos, con búsqueda, filtros y orden
    public function index(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'buscar'          => 'nullable|string|max:150',
            'categoria_id'    => 'nullable|integer|exists:categorias,id',
            'subcategoria_id' => 'nullable|integer|exists:categorias,id',
            'marca_id'        => 'nullable|integer|exists:marcas,id',
            'precio_min'      => 'nullable|numeric|min:0',
            'precio_max'      => 'nullable|numeric|min:0',
            'orden'           => 'nullable|in:precio_asc,precio_desc,nombre_asc',
            'limite'          => 'nullable|integer|min:1|max:100'
        ]);

        // Catálogo del cliente: solo productos activos y con stock (CU-GP-C-001)
        $query = Producto::with(['categoria', 'marca'])->where('estado', 'activo')->where('stock', '>', 0);

        // Búsqueda por nombre o descripción (CU-GP-C-004)
        if (!empty($filtros['buscar'])) {
            $termino = '%' . $filtros['buscar'] . '%';
            $query->where(function ($q) use ($termino) {
                $q->where('nombre', 'like', $termino)
                  ->orWhere('descripcion', 'like', $termino);
            });
        }

        // Subcategoría tiene prioridad; si no, categoría incluye sus subcategorías
        if (!empty($filtros['subcategoria_id'])) {
            $query->where('categoria_id', $filtros['subcategoria_id']);
        } elseif (!empty($filtros['categoria_id'])) {
            $ids = Categoria::where('padre_id', $filtros['categoria_id'])->pluck('id')->push((int) $filtros['categoria_id']);
            $query->whereIn('categoria_id', $ids);
        }

        if (!empty($filtros['marca_id'])) {
            $query->where('marca_id', $filtros['marca_id']);
        }

        // Rango de precios
        if (isset($filtros['precio_min'])) {
            $query->where('precio', '>=', $filtros['precio_min']);
        }
        if (isset($filtros['precio_max'])) {
            $query->where('precio', '<=', $filtros['precio_max']);
        }

        // Ordenamiento
        match ($filtros['orden'] ?? null) {
            'precio_asc'  => $query->orderBy('precio', 'asc'),
            'precio_desc' => $query->orderBy('precio', 'desc'),
            'nombre_asc'  => $query->orderBy('nombre', 'asc'),
            default       => $query->orderBy('id', 'desc'),
        };

        if (!empty($filtros['limite'])) {
            $query->limit((int) $filtros['limite']);
        }

        return response()->json($query->get());
    }

    // Listado para el admin: incluye productos inactivos
    public function indexAdmin(): JsonResponse
    {
        $productos = Producto::with(['categoria', 'marca'])->get();
        return response()->json($productos);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'categoria_id' => 'required|exists:categorias,id',
                'marca_id'     => 'required|exists:marcas,id', // <-- Validación agregada
                'nombre'       => 'required|string|max:150|unique:productos,nombre',
                'descripcion'  => 'required|string',
                'precio'       => 'required|numeric|gt:0',
                'stock'        => 'required|integer|min:0',
                'url_imagen'   => 'nullable|url|max:255',
                'estado'       => 'required|in:activo,inactivo'
            ]);
            $producto = Producto::create($validated);
            return response()->json($producto, 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el producto',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Detalle público: un producto inactivo se trata como no encontrado
    public function show($id): JsonResponse
    {
        $producto = Producto::with(['categoria', 'marca'])->where('estado', 'activo')->find($id);
        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        return response()->json($producto);
    }
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $producto = Producto::find($id);
            if (!$producto) {
                return response()->json(['message' => 'Producto no encontrado'], 404);
            }
            $validated = $request->validate([
                'categoria_id' => 'required|exists:categorias,id',
                'marca_id'     => 'required|exists:marcas,id', // <-- Validación agregada
                'nombre'       => 'required|string|max:150|unique:productos,nombre,' . $id,
                'descripcion'  => 'required|string',
                'precio'       => 'required|numeric|gt:0',
                'stock'        => 'required|integer|min:0',
                'url_imagen'   => 'nullable|url|max:255',
                'estado'       => 'required|in:activo,inactivo'
            ]);
            $producto->update($validated);
            return response()->json($producto);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el producto',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id): JsonResponse
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        $producto->delete();
        return response()->json(['message' => 'Producto eliminado'], 200);
    }
}
