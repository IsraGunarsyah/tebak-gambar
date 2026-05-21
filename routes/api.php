<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/hit-buzzer', [GameController::class, 'hitBuzzer']);
// Tambahkan baris ini:
Route::post('/set-mode', [GameController::class, 'setGameMode']);
// ... rute sebelumnya ...
Route::post('/join-room', [GameController::class, 'joinRoom']);
Route::get('/get-players', [GameController::class, 'getPlayers']);
Route::post('/reset-players', [GameController::class, 'resetPlayers']); // Buat jaga-jaga kalau mau gladi bersih
Route::post('/show-image', [GameController::class, 'showImage']);
