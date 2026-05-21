<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;

Route::get('/', function () {
    return view('welcome');
});

// 1. Layar HP Peserta (Paling ringan, tanpa gambar)
Route::get('/play', [GameController::class, 'playerView'])->name('play');

// 2. Layar Videotron (Penuh gambar, dikontrol admin)
Route::get('/videotron', [GameController::class, 'videotronView'])->name('videotron');

// 3. Layar Mac Anda (Panel Sakelar & Kendali)
Route::get('/admin', [GameController::class, 'adminDashboard'])->name('admin.index');

