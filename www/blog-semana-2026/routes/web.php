<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BlogController::class, 'home'])->name('home');
Route::get('/entradas', [BlogController::class, 'index'])->name('blog.index');
Route::get('/entradas/{post}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/evidencias', [EvidenceController::class, 'index'])->name('evidences.index');

Route::middleware('guest')->group(function () {
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])
        ->middleware('throttle:6,1')
        ->name('register.store');

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
    Route::get('/panel/crud', [PanelController::class, 'crud'])->name('panel.crud');
    Route::get('/crud', [PanelController::class, 'crud'])->name('crud.index');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::resource('posts', PostController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');

    // Evidencias
    Route::get('/evidencias/crear', [EvidenceController::class, 'create'])->name('evidences.create');
    Route::post('/evidencias', [EvidenceController::class, 'store'])->name('evidences.store');
    Route::get('/evidencias/{evidence}/editar', [EvidenceController::class, 'edit'])->name('evidences.edit');
    Route::put('/evidencias/{evidence}', [EvidenceController::class, 'update'])->name('evidences.update');
    Route::delete('/evidencias/{evidence}', [EvidenceController::class, 'destroy'])->name('evidences.destroy');
});