<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CardsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('users')->group(function(){
    Route::post('/login',[UsersController::class,'login']);
    Route::put('/crear',[UsersController::class, 'crear']); 
    Route::get('/recuperarPassword',[UsersController::class, 'recuperarPassword']);
    Route::get('/busquedaNombre',[CardsController::class,'busquedaNombre']);
    Route::get('/busquedaVenta',[CardsController::class,'busquedaVenta']);
});

Route::middleware(['apitoken','permission'])->prefix('users')->group(function(){
    Route::put('/crearCarta',[CardsController::class,'crearCarta']);
    Route::put('/crearColeccion',[CardsController::class,'crearColeccion']);
    //Route::get('/detalle/{id}',[UsersController::class, 'detalle']);
});

Route::middleware(['apitoken','permission2'])->prefix('users')->group(function(){
    Route::put('/comprarCarta',[UsersController::class,'comprarCarta']);
    Route::put('/ventaCarta',[UsersController::class,'ventaCarta']);
    //Route::get('/detalle/{id}',[UsersController::class, 'detalle']);
});
