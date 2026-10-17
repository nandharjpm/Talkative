<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

    Route::get('/auth/google/redirect', [AuthController::class,'redirectToGoogle'])->name('google.redirect');

    Route::get('/auth/google/callback', [AuthController::class,'handleGoogleCallback'])->name('google.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('/chat', function () {
        return view('chat.index');
    })->name('chat.index');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});

Route::get('/', function () {
    return redirect()->route('login');
});
