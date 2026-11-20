<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BlogController::class, 'home'])->name('home');
Route::get('/entradas', [BlogController::class, 'index'])->name('blog.index');
Route::get('/entradas/{post}', [BlogController::class, 'show'])->name('blog.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.store');

    // Recuperación de contraseña
    Route::get('/recuperar-clave', [AuthController::class, 'showForgotPassword'])
        ->name('password.request');
    Route::post('/recuperar-clave', [AuthController::class, 'sendResetLink'])
        ->middleware('throttle:6,1')
        ->name('password.email');
    Route::get('/restablecer-clave/{token}', [AuthController::class, 'showResetPassword'])
        ->name('password.reset');
    Route::post('/restablecer-clave', [AuthController::class, 'resetPassword'])
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/panel', [PanelController::class, 'index'])->name('panel.index');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::resource('posts', PostController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');
});