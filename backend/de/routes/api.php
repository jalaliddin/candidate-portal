<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminFaqController;
use App\Http\Controllers\AdminOccupationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

// Telegram webhook — public, no auth required
Route::post('/telegram/webhook', [TelegramController::class, 'webhook']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/me/language', [AuthController::class, 'updateLanguage']);

    Route::get('/occupations', [AdminOccupationController::class, 'list']);

    Route::get('/candidates/filter-options', [CandidateController::class, 'filterOptions']);
    Route::get('/candidates', [CandidateController::class, 'index']);
    Route::get('/candidates/{id}', [CandidateController::class, 'show']);

    Route::get('/requests', [RequestController::class, 'index']);
    Route::post('/requests', [RequestController::class, 'store']);
    Route::patch('/requests/{id}', [RequestController::class, 'update']);

    Route::get('/reports/candidates', [ReportController::class, 'candidateList']);
    Route::get('/reports/requests-stats', [ReportController::class, 'requestsStats']);
    Route::get('/reports/candidate-profile/{id}', [ReportController::class, 'candidateProfile']);

    Route::middleware('admin')->prefix('admin')->group(function (): void {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/candidates', [AdminController::class, 'candidateIndex']);
        Route::post('/candidates', [AdminController::class, 'candidateStore']);
        Route::put('/candidates/{id}', [AdminController::class, 'candidateUpdate']);
        Route::delete('/candidates/{id}', [AdminController::class, 'candidateDestroy']);
        Route::get('/users', [AdminController::class, 'userIndex']);
        Route::post('/users', [AdminController::class, 'userStore']);
        Route::patch('/users/{id}', [AdminController::class, 'userUpdate']);
        Route::delete('/users/{id}', [AdminController::class, 'userDestroy']);
        Route::get('/requests', [RequestController::class, 'adminIndex']);

        // Occupation management
        Route::get('/occupations', [AdminOccupationController::class, 'index']);
        Route::post('/occupations', [AdminOccupationController::class, 'store']);
        Route::put('/occupations/{id}', [AdminOccupationController::class, 'update']);
        Route::delete('/occupations/{id}', [AdminOccupationController::class, 'destroy']);

        // FAQ management
        Route::get('/faqs', [AdminFaqController::class, 'index']);
        Route::post('/faqs', [AdminFaqController::class, 'store']);
        Route::put('/faqs/{id}', [AdminFaqController::class, 'update']);
        Route::delete('/faqs/{id}', [AdminFaqController::class, 'destroy']);
    });
});
