<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class MarcaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $marcas = Marca::all();
        return response()->json($marcas);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:150|unique:marcas,nombre',
                'descripcion' => 'nullable|string',
                'estado' => 'required|in:activo,inactivo'
            ]);

            $marca = Marca::create($request->all());

            return response()->json($marca, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear la marca', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        $marca = Marca::find($id);
        if (!$marca) {
            return response()->json(['message' => 'Marca no encontrada'], 404);
        }
        return response()->json($marca);
    }

    public function update(Request $request, $id)
    {
        try {
            $marca = Marca::find($id);
            if (!$marca) {
                return response()->json(['message' => 'Marca no encontrada'], 404);
            }

            $request->validate([
                'nombre' => 'required|string|max:150|unique:marcas,nombre,' . $id,
                'descripcion' => 'nullable|string',
                'estado' => 'required|in:activo,inactivo'
            ]);

            $marca->update($request->all());

            return response()->json($marca);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar la marca', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $marca = Marca::find($id);
        if (!$marca) {
            return response()->json(['message' => 'Marca no encontrada'], 404);
        }
        $marca->delete();
        return response()->json(['message' => 'Marca eliminada'], 204);
    }
    
}
