<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// public routes (no authentication required)
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'user']);

    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);   // List tasks with search & pagination
        Route::post('/', [TaskController::class, 'store']);  // Create a new task
        Route::get('{task}', [TaskController::class, 'show']); // Show a specific task
        Route::put('{task}', [TaskController::class, 'update']); // Update a task
        Route::delete('{task}', [TaskController::class, 'destroy']); // Delete a task
    });
});
