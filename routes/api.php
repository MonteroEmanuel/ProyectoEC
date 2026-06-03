<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\MarcaController;

// Categorias Routes
Route::post('/categorias', [CategoriaController::class, 'store']);
Route::put('/categorias/{id}', [CategoriaController::class, 'update']);
Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy']);
Route::get('/categorias', [CategoriaController::class, 'index']);
Route::get('/categorias/{id}', [CategoriaController::class, 'show']);

// Productos Routes
Route::post('/productos', [ProductoController::class, 'store']);
Route::put('/productos/{id}', [ProductoController::class, 'update']);
Route::get('/productos', [ProductoController::class, 'index']);
Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);
Route::get('/productos/{id}', [ProductoController::class, 'show']);

// Marcas Routes
Route::post('/marcas', [MarcaController::class, 'store']);
Route::put('/marcas/{id}', [MarcaController::class, 'update']);
Route::get('/marcas', [MarcaController::class, 'index']);
Route::delete('/marcas/{id}', [MarcaController::class, 'destroy']);
Route::get('/marcas/{id}', [MarcaController::class, 'show']);