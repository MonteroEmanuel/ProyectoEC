<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    public function index(): JsonResponse{
        $categorias = Categoria::all();
        return response()->json($categorias);
    }
    
    public function store(Request $request): JsonResponse{
        $request->validate([
            'nombre' => 'required|string|max:50|unique:categorias,nombre',
        ]);

        $categoria = Categoria::create($request->all());

        return response()->json($categoria, 201);
    }


    public function show($id): JsonResponse{
        $categoria = Categoria::find($id);
        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }
        return response()->json($categoria);
    }


    public function update(Request $request, $id): JsonResponse{
        $categoria = Categoria::find($id);
        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        $request->validate([
            'nombre' => 'required|string|max:50|unique:categorias,nombre,' . $id,
        ]);

        $categoria->update($request->all());

        return response()->json($categoria, 200);
    }

    public function destroy($id): JsonResponse{
        $categoria = Categoria::find($id);
        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }
        $categoria->delete();
        return response()->json(null,204);
    }
}