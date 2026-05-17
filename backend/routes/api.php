<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ─── Auth ──────────────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        // Strict limits to prevent brute-force and registration spam
        Route::middleware('throttle:5,1')->group(function () {
            Route::post('/register', [AuthController::class, 'register']);
            Route::post('/login',    [AuthController::class, 'login']);
        });

        // Refresh uses a DB refresh token — access token may be expired, so no auth:api here
        Route::post('/refresh', [AuthController::class, 'refresh'])
            ->middleware('throttle:10,1');

        Route::middleware(['auth:api', 'throttle:30,1'])->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me',      [AuthController::class, 'me']);
        });
    });

    // ─── Posts (public) ────────────────────────────────────────────────────
    // GET  /api/v1/posts?search=&tag=&per_page=
    // GET  /api/v1/posts/{slug}
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/posts',        [PostController::class, 'index']);
        Route::get('/posts/{slug}', [PostController::class, 'show']);
    });

    // ─── Posts (authenticated) ─────────────────────────────────────────────
    // POST   /api/v1/posts                    multipart/form-data
    // PUT    /api/v1/posts/{slug}              application/json
    // POST   /api/v1/posts/{slug}?_method=PUT  multipart/form-data (file upload)
    // DELETE /api/v1/posts/{slug}
    Route::middleware(['auth:api', 'throttle:20,1'])->group(function () {
        Route::post('/posts',          [PostController::class, 'store']);
        Route::match(['PUT', 'PATCH'], '/posts/{slug}', [PostController::class, 'update']);
        Route::delete('/posts/{slug}', [PostController::class, 'destroy']);
    });

    // ─── Comments ──────────────────────────────────────────────────────────
    Route::get('/posts/{slug}/comments', [CommentController::class, 'index'])
        ->middleware('throttle:60,1');

    // Strict limit to prevent comment spam
    Route::post('/posts/{slug}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:10,1');

    // ─── Tags ──────────────────────────────────────────────────────────────
    Route::get('/tags', [TagController::class, 'index'])
        ->middleware('throttle:60,1');
});
