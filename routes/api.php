<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilyController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::prefix('/families')->group(function () {
    Route::get('/', [FamilyController::class, 'index']);
    Route::get('/detail/{id}', [FamilyController::class, 'detail']);
    Route::post('/store', [FamilyController::class, 'store']);
    Route::put('/update/{id}', [FamilyController::class, 'update']);
    Route::delete('/delete/{id}', [FamilyController::class, 'delete']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
