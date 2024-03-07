<?php

use App\Http\Controllers\DudasController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\SearcherController;
use App\Http\Controllers\SugerenciasController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index']);

Route::get('/dudas', [DudasController::class, 'index']);
Route::get('/sugerencias', [SugerenciasController::class, 'index']);
Route::get('/recurso', [ResourceController::class, 'index']);

Route::get('/buscador', [SearcherController::class, 'index']);
