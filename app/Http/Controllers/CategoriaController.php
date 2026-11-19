<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CategoriaController extends Controller
{
    public function index(): JsonResponse{
        $categorias = Categoria::all();
        return response()->json($categorias);
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre'   => 'required|string|max:50|unique:categorias,nombre',
                'padre_id' => 'nullable|integer|exists:categorias,id',
            ]);

            $categoria = Categoria::create($validated);

            return response()->json([
                'message' => 'Categoría creada con éxito',
                'data'    => $categoria
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear la categoría', 'error' => $e->getMessage()], 500);
        }
    }

    // Categorías principales con sus subcategorías (para filtros de la tienda)
     public function showParentCategories()
    {
        $categorias = Categoria::whereNull('padre_id')->with('subcategorias')->get();
        return response()->json($categorias);
    }

    public function subcategorias($id): JsonResponse
    {
        $categoria = Categoria::find($id);
        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }
        return response()->json($categoria->subcategorias()->get());
    }

    public function show($id): JsonResponse{
        $categoria = Categoria::find($id);
        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }
        return response()->json($categoria);
    }


    public function update(Request $request, $id){
        try {
            $categoria = Categoria::find($id);
            if (!$categoria) {
                return response()->json(['message' => 'Categoría no encontrada'], 404);
            }

            $validated = $request->validate([
                'nombre'   => 'required|string|max:50|unique:categorias,nombre,' . $id,
                'padre_id' => 'nullable|integer|exists:categorias,id|not_in:' . $id,
            ]);

            $categoria->update($validated);

            return response()->json([
                'message' => 'Categoría actualizada con éxito',
                'data'    => $categoria
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar la categoría', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse{
        $categoria = Categoria::find($id);
        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        // Evita borrar (en cascada) productos asociados a la categoría o a sus subcategorías
        $ids = $categoria->subcategorias()->pluck('id')->push($categoria->id);
        if (Producto::whereIn('categoria_id', $ids)->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar la categoría porque tiene productos asignados.'
            ], 400);
        }

        $categoria->delete();
        return response()->json([
            'message' => 'Categoría eliminada'
        ], 200 );
    }
}
