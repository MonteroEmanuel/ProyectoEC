<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PedidoController extends Controller
{
    // Cliente: crea un pedido a partir del carrito.
    // Valida stock y precios contra la BD y descuenta el stock (RF-GP-006).
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'direccion_envio'     => 'required|string|min:5|max:255',
            'items'               => 'required|array|min:1',
            'items.*.producto_id' => 'required|integer|distinct|exists:productos,id',
            'items.*.cantidad'    => 'required|integer|min:1',
        ]);

        $pedido = DB::transaction(function () use ($datos, $request) {
            $total = 0;
            $lineas = [];

            foreach ($datos['items'] as $i => $item) {
                // Bloqueo de fila para evitar vender más unidades que el stock
                $producto = Producto::lockForUpdate()->find($item['producto_id']);

                if ($producto->estado !== 'activo') {
                    throw ValidationException::withMessages([
                        "items.$i.producto_id" => ["«{$producto->nombre}» ya no está disponible."],
                    ]);
                }
                if ($producto->stock < $item['cantidad']) {
                    throw ValidationException::withMessages([
                        "items.$i.cantidad" => ["Solo quedan {$producto->stock} unidades de «{$producto->nombre}»."],
                    ]);
                }

                $subtotal = $producto->precio * $item['cantidad'];
                $total += $subtotal;
                $lineas[] = [
                    'producto_id'     => $producto->id,
                    'nombre'          => $producto->nombre,
                    'precio_unitario' => $producto->precio,
                    'cantidad'        => $item['cantidad'],
                    'subtotal'        => $subtotal,
                ];

                $producto->decrement('stock', $item['cantidad']);
            }

            $pedido = Pedido::create([
                'user_id'         => $request->user()->id,
                'estado'          => 'pendiente',
                'total'           => $total,
                'direccion_envio' => $datos['direccion_envio'],
            ]);
            $pedido->detalles()->createMany($lineas);

            return $pedido;
        });

        return response()->json([
            'message' => 'Pedido realizado con éxito',
            'data'    => $pedido->load('detalles'),
        ], 201);
    }

    // Cliente: sus propios pedidos
    public function misPedidos(Request $request): JsonResponse
    {
        $pedidos = Pedido::with('detalles')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();

        return response()->json($pedidos);
    }

    // Cliente (dueño) o admin
    public function show(Request $request, $id): JsonResponse
    {
        $pedido = Pedido::with(['detalles', 'usuario:id,name,email'])->find($id);

        if (!$pedido || !$this->puedeVer($request, $pedido)) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }
        return response()->json($pedido);
    }

    // Cliente: cancela su pedido mientras esté pendiente
    public function cancelar(Request $request, $id): JsonResponse
    {
        $pedido = Pedido::find($id);

        if (!$pedido || $pedido->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }
        if ($pedido->estado !== 'pendiente') {
            return response()->json(['message' => 'Solo se pueden cancelar los pedidos pendientes.'], 400);
        }

        $this->cancelarYReponerStock($pedido);

        return response()->json(['message' => 'Pedido cancelado', 'data' => $pedido->load('detalles')]);
    }

    // Admin: listado con filtro por estado
    public function indexAdmin(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'estado' => 'nullable|in:' . implode(',', Pedido::ESTADOS),
        ]);

        $query = Pedido::with(['detalles', 'usuario:id,name,email'])->orderByDesc('id');

        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        return response()->json($query->get());
    }

    // Admin: cambia el estado respetando las transiciones permitidas
    public function cambiarEstado(Request $request, $id): JsonResponse
    {
        $datos = $request->validate([
            'estado' => 'required|in:' . implode(',', Pedido::ESTADOS),
        ]);

        $pedido = Pedido::find($id);
        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        if (!in_array($datos['estado'], Pedido::TRANSICIONES[$pedido->estado])) {
            return response()->json([
                'message' => "No se puede pasar un pedido de «{$pedido->estado}» a «{$datos['estado']}»."
            ], 400);
        }

        if ($datos['estado'] === 'cancelado') {
            $this->cancelarYReponerStock($pedido);
        } else {
            $pedido->update(['estado' => $datos['estado']]);
        }

        return response()->json([
            'message' => 'Estado del pedido actualizado',
            'data'    => $pedido->load(['detalles', 'usuario:id,name,email']),
        ]);
    }

    private function puedeVer(Request $request, Pedido $pedido): bool
    {
        return $request->user()->hasRole('admin') || $pedido->user_id === $request->user()->id;
    }

    // Cancelar devuelve las unidades al inventario
    private function cancelarYReponerStock(Pedido $pedido): void
    {
        DB::transaction(function () use ($pedido) {
            foreach ($pedido->detalles as $detalle) {
                if ($detalle->producto_id) {
                    Producto::where('id', $detalle->producto_id)->increment('stock', $detalle->cantidad);
                }
            }
            $pedido->update(['estado' => 'cancelado']);
        });
    }
}
