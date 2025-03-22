<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// public routes (no authentication required)
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');

// protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'user']);

    // Task Routes and has a prefix of tasks
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);   // List tasks with search & pagination
        Route::post('/', [TaskController::class, 'store']);  // Creating a new task
        Route::get('{task}', [TaskController::class, 'show']); // Showing a specific task
        Route::put('{task}', [TaskController::class, 'update']); // Updating a task
        Route::delete('{task}', [TaskController::class, 'destroy']); // Deleting a task
    });
});
