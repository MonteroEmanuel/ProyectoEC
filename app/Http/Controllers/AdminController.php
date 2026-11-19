<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    // Umbral de stock bajo (RF-PA-005)
    private const STOCK_BAJO = 5;

    // RF-PA-002 y RF-PA-005: métricas del dashboard
    public function dashboard(): JsonResponse
    {
        $activos = Producto::where('estado', 'activo');

        $stockBajo = (clone $activos)
            ->whereBetween('stock', [1, self::STOCK_BAJO])
            ->with(['categoria', 'marca'])
            ->orderBy('stock')
            ->orderBy('nombre');

        // El módulo de pedidos aún no existe: se informa null hasta que haya tabla
        $pedidosPendientes = Schema::hasTable('pedidos')
            ? DB::table('pedidos')->where('estado', 'pendiente')->count()
            : null;

        return response()->json([
            'pedidos_pendientes'  => $pedidosPendientes,
            'productos_agotados'  => (clone $activos)->where('stock', 0)->count(),
            'usuarios_nuevos'     => User::role('client')->where('created_at', '>=', now()->subDays(7))->count(),
            'productos_activos'   => (clone $activos)->count(),
            'umbral_stock_bajo'   => self::STOCK_BAJO,
            'total_stock_bajo'    => (clone $stockBajo)->count(),
            'stock_bajo'          => $stockBajo->limit(10)->get(),
        ]);
    }

    // RF-PA-004: búsqueda por ID de pedido, nombre de producto o email de cliente
    public function buscar(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'q' => 'required|string|max:150',
        ]);
        $q = trim($datos['q']);

        $productos = Producto::with(['categoria', 'marca'])
            ->where(function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%");
                if (ctype_digit($q)) {
                    $query->orWhere('id', (int) $q);
                }
            })
            ->orderBy('nombre')
            ->limit(10)
            ->get();

        $usuarios = User::with('roles:id,name')
            ->where('email', 'like', "%{$q}%")
            ->orderBy('email')
            ->limit(10)
            ->get(['id', 'name', 'email', 'created_at'])
            ->map(fn ($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'created_at' => $u->created_at,
                'roles'      => $u->roles->pluck('name'),
            ]);

        $pedidos = [];
        if (Schema::hasTable('pedidos') && ctype_digit($q)) {
            $pedidos = DB::table('pedidos')->where('id', (int) $q)->get();
        }

        return response()->json([
            'productos' => $productos,
            'usuarios'  => $usuarios,
            'pedidos'   => $pedidos,
        ]);
    }
}
