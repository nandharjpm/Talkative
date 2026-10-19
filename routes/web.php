
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MessageAttachmentController;
use App\Http\Controllers\ChatRequestController;
use App\Http\Controllers\UserSearchController;
use App\Http\Controllers\CallController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

    Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])
        ->name('google.redirect');

    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])
        ->name('google.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('chat.index'));

    Route::get('/chat', [ConversationController::class, 'index'])
        ->name('chat.index');

    Route::get('/users/search', [UserSearchController::class, 'search'])
        ->name('users.search');

    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'index'])
        ->name('messages.index');

    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store'])
        ->name('messages.store');

    Route::post('/conversations/{conversation}/attachments', [MessageAttachmentController::class, 'store'])
        ->name('attachments.store');

    Route::get('/chat-requests', [ChatRequestController::class, 'index'])
        ->name('chat-requests.index');

    Route::post('/chat-requests', [ChatRequestController::class, 'store'])
        ->name('chat-requests.store');

    Route::post('/chat-requests/{chatRequest}/accept', [ChatRequestController::class, 'accept'])
        ->name('chat-requests.accept');

    Route::post('/chat-requests/{chatRequest}/reject', [ChatRequestController::class, 'reject'])
        ->name('chat-requests.reject');

    Route::post('/calls', [CallController::class, 'store'])
        ->name('calls.store');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});
