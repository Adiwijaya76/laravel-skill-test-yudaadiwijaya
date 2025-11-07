<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Post\PostController; 

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});


Route::resource('posts', PostController::class)->only(['index', 'show', 'create', 'edit']);

// 🔹 Protected routes (dengan auth)
Route::middleware('auth')->group(function () {
   Route::resource('posts', PostController::class)->only(['store', 'update', 'destroy']);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
