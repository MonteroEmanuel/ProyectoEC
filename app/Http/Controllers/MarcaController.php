<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MarcaController extends Controller
{
    public function index(): JsonResponse
    {
        $marcas = Marca::all();
        return response()->json($marcas);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:100|unique:marcas,nombre'
            ]);

            $marca = Marca::create($validated);

            return response()->json([
                'message' => 'Marca creada con éxito',
                'data'    => $marca
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la marca',
                'error'   => $e->getMessage()
            ], 500);
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

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $marca = Marca::find($id);
            if (!$marca) {
                return response()->json(['message' => 'Marca no encontrada'], 404);
            }

            $validated = $request->validate([
                'nombre' => 'required|string|max:100|unique:marcas,nombre,' . $id
            ]);

            $marca->update($validated);

            return response()->json([
                'message' => 'Marca actualizada con éxito',
                'data'    => $marca
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la marca',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $marca = Marca::find($id);
        if (!$marca) {
            return response()->json(['message' => 'Marca no encontrada'], 404);
        }

        // Evita borrar la marca si ya tiene productos asignados
        if ($marca->productos()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar la marca porque tiene productos asignados.'
            ], 400);
        }

        $marca->delete();

        return response()->json([
            'message' => 'Marca eliminada con éxito'
        ], 200);
    }
}
