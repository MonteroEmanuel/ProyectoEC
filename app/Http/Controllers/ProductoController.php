<?php

namespace App\Http\Controllers;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class ProductoController extends Controller
{
    public function index(): JsonResponse
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
                'precio'       => 'required|numeric|min:0',
                'stock'        => 'required|integer|min:0',
                'url_imagen'   => 'nullable|url|max:255',
                'estado'       => 'required|in:activo,inactivo'
            ]);
            $producto = Producto::create($validated);
            return response()->json($producto, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el producto', 
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function show($id): JsonResponse
    {
        $producto = Producto::with(['categoria', 'marca'])->find($id);
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
                'precio'       => 'required|numeric|min:0',
                'stock'        => 'required|integer|min:0',
                'url_imagen'   => 'nullable|url|max:255',
                'estado'       => 'required|in:activo,inactivo'
            ]);
            $producto->update($validated);
            return response()->json($producto);
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
        return response()->json(['message' => 'Producto eliminado'], 204);
    }
}