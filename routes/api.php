<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\UserController;

// Rutas Públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas públicas de consulta (tienda: catálogo, filtros y detalle)
Route::get('/categorias', [CategoriaController::class, 'index']);
Route::get('/categorias/padre', [CategoriaController::class, 'showParentCategories']);
Route::get('/categorias/{id}/subcategorias', [CategoriaController::class, 'subcategorias']);
Route::get('/categorias/{id}', [CategoriaController::class, 'show']);

Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/{id}', [ProductoController::class, 'show']);

Route::get('/marcas', [MarcaController::class, 'index']);
Route::get('/marcas/{id}', [MarcaController::class, 'show']);

// Rustas protegidas por autenticación  (Requiere token de acceso)
Route::middleware('auth:sanctum')->group(function () {

//info del usuario autenticado
Route::get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/logout', [AuthController::class, 'logout']);

// Pedidos del cliente autenticado
Route::post('/pedidos', [PedidoController::class, 'store']);
Route::get('/mis-pedidos', [PedidoController::class, 'misPedidos']);
Route::get('/pedidos/{id}', [PedidoController::class, 'show']);
Route::put('/pedidos/{id}/cancelar', [PedidoController::class, 'cancelar']);

// Rutas de administración (requieren rol admin)
Route::middleware('role:admin')->group(function () {

    // Dashboard de KPIs y búsqueda consolidada
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/buscar', [AdminController::class, 'buscar']);

    // Gestión de usuarios
    Route::get('/admin/usuarios', [UserController::class, 'index']);
    Route::post('/admin/usuarios', [UserController::class, 'store']);
    Route::put('/admin/usuarios/{id}', [UserController::class, 'update']);
    Route::delete('/admin/usuarios/{id}', [UserController::class, 'destroy']);

    // Gestión de pedidos
    Route::get('/admin/pedidos', [PedidoController::class, 'indexAdmin']);
    Route::put('/admin/pedidos/{id}/estado', [PedidoController::class, 'cambiarEstado']);

    // Listado completo de productos (incluye inactivos)
    Route::get('/admin/productos', [ProductoController::class, 'indexAdmin']);

    // Categorias Routes
    Route::post('/categorias', [CategoriaController::class, 'store']);
    Route::put('/categorias/{id}', [CategoriaController::class, 'update']);
    Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy']);

    // Productos Routes
    Route::post('/productos', [ProductoController::class, 'store']);
    Route::put('/productos/{id}', [ProductoController::class, 'update']);
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);

    // Marcas Routes
    Route::post('/marcas', [MarcaController::class, 'store']);
    Route::put('/marcas/{id}', [MarcaController::class, 'update']);
    Route::delete('/marcas/{id}', [MarcaController::class, 'destroy']);
});

});
