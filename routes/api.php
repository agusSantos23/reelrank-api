<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\SagaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserMovieController;
use App\Http\Middleware\CheckActionLimit;
use App\Http\Middleware\EnsureTokenIsValid;


// User
Route::get('/auth/token/{id}', [UserController::class, 'show']);


// Auth
Route::post('/auth/register', [AuthController::class, 'register']);

Route::post('/auth/login', [AuthController::class, 'login']);


// Movie
Route::resource('/movies', MovieController::class);

Route::get('/movies/{movieId}/{userId?}', [MovieController::class, 'show']);


// Genre
Route::resource('/genres', GenreController::class);


// Saga
Route::get('/sagas', [SagaController::class, 'index']);

// Movie of User
Route::middleware(EnsureTokenIsValid::class)->group(function () {

  // Unblock User
  Route::post('/user/{userId}/unblock', [UserController::class, 'unblock']);

  Route::middleware(CheckActionLimit::class)->group(function () {
    
    // Rate 
    Route::patch('/usermovies/{userId}/{movieId}/rate', [UserMovieController::class, 'submitRating']);

    // Favorite
    Route::patch('/usermovies/{userId}/{movieId}/favorite', [UserMovieController::class, 'toggleFavorite']);

    // ToWatch
    Route::patch('/usermovies/{userId}/{movieId}/seen', [UserMovieController::class, 'toggleSeen']);

  });

});

