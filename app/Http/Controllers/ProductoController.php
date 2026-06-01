<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\jsonResponse;
use illuminate\Validation\Rule;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with('categoria')->get();
        return response()->json($productos);
    }

    public function store(Request $request){
        try {
            $request->validate([
                'categoria_id' => 'required|exists:categorias,id',
                'nombre' => 'required|string|max:150|unique:productos,nombre',
                'descripcion' => 'required|string',
                'precio' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'url_imagen' => 'nullable|url|max:255',
                'estado' => 'required|in:activo,inactivo'
            ]);

            $producto = Producto::create($request->all());

            return response()->json($producto, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear el producto', 'error' => $e->getMessage()], 500);
        }
    }





    public function show($id): JsonResponse
    {
        $producto = Producto::with('categoria')->find($id);
        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        return response()->json($producto);
    }

    public function update(Request $request, $id){
        try {
            $producto = Producto::find($id);
            if (!$producto) {
                return response()->json(['message' => 'Producto no encontrado'], 404);
            }

            $request->validate([
                'categoria_id' => 'required|exists:categorias,id',
                'nombre' => 'required|string|max:150|unique:productos,nombre,' . $id,
                'descripcion' => 'required|string',
                'precio' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'url_imagen' => 'nullable|url|max:255',
                'estado' => 'required|in:activo,inactivo'
            ]);

            $producto->update($request->all());

            return response()->json($producto);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar el producto', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        $producto->delete();
        return response()->json([
            'message' => 'Producto eliminado'
        ], 204);

    }
}