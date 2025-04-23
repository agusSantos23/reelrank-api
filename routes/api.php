<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\SagaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\UserController;

// User
Route::get('/auth/token/{id}', [UserController::class, 'show']);


// Auth
Route::post('/auth/register', [AuthController::class, 'register']);

Route::post('/auth/login', [AuthController::class, 'login']);


// Movie
Route::resource('/movies', MovieController::class);

Route::get('/movies/{movie}', [MovieController::class, 'show']);


// Genre
Route::resource('/genres', GenreController::class);


// Saga
Route::get('/sagas', [SagaController::class, 'index']);
