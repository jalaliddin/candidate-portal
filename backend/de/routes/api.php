<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/me/language', [AuthController::class, 'updateLanguage']);

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
    });
});
