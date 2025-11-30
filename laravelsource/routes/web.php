<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ForumThreadController;
use App\Http\Controllers\ForumPostController;

// Maintenance status API endpoint (publicly accessible)
Route::get('/api/maintenance/status', [MaintenanceController::class, 'status'])->name('maintenance.status');

Route::get('/', [AuthController::class, 'showLoginForm'])->name('welcome');

Route::middleware(['guest', 'App\Http\Middleware\CheckMaintenanceMode'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::get('/auth/{provider}', [SocialAuthController::class, 'redirectToProvider'])->name('auth.social');
    Route::get('/password/reset', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback'])->name('auth.callback');

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/game', [AuthController::class, 'showGame'])
        ->middleware(['verified', 'App\Http\Middleware\CheckMaintenanceMode'])
        ->name('game.index');

    Route::get('/email/verify', [AuthController::class, 'showVerifyEmailNotice'])
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware('signed')
        ->name('verification.verify');

    Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::post('/email/verify-code', [AuthController::class, 'verifyEmailWithCode'])
        ->middleware('throttle:6,1')
        ->name('verification.verify-code');

    Route::post('/email/resend-code', [AuthController::class, 'resendVerificationCode'])
        ->middleware('throttle:6,1')
        ->name('verification.resend-code');
});

Route::prefix('forum')->name('forum.')->group(function () {
    Route::get('/', [ForumController::class, 'index'])->name('index');
    Route::get('/search', [ForumController::class, 'search'])->name('search');
    Route::get('/category/{slug}', [ForumController::class, 'category'])->name('category');
    Route::get('/thread/{slug}', [ForumThreadController::class, 'show'])->name('thread.show');
    
    Route::middleware('auth')->group(function () {
        Route::get('/category/{categorySlug}/create-thread', [ForumThreadController::class, 'create'])->name('thread.create');
        Route::post('/category/{categorySlug}/create-thread', [ForumThreadController::class, 'store'])->name('thread.store');
        Route::post('/thread/{thread}/like', [ForumThreadController::class, 'like'])->name('thread.like');
        Route::post('/thread/{thread}/reply', [ForumPostController::class, 'store'])->name('post.store');
        Route::post('/post/{post}/like', [ForumPostController::class, 'like'])->name('post.like');
        Route::delete('/post/{post}', [ForumPostController::class, 'destroy'])->name('post.destroy');
    });
});