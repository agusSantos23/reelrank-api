<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SagaController;
use App\Http\Controllers\UserGenreController;
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

// Verify Token
Route::post('/auth/verify-token', [AuthController::class, 'verifyToken']);


// Movie
Route::resource('/movies', MovieController::class);

Route::get('/movies/{movieId}/{userId?}', [MovieController::class, 'show']);


// Genre
Route::get('/genres/{userId?}', [UserGenreController::class, 'index']);


// Saga
Route::get('/sagas', [SagaController::class, 'index']);

// Movie of User
Route::middleware(EnsureTokenIsValid::class)->group(function () {

  // Unblock User
  Route::post('/user/{userId}/unblock', [UserController::class, 'unblock']);

  // User Movies
  Route::get('/user/movies/{userId}', [UserMovieController::class,'index']);

  // Statistics User
  Route::get('/user/{userId}/statistics', [UserController::class, 'userStatistics']);

  Route::middleware(CheckActionLimit::class)->group(function () {
    
    // Rate Movie
    Route::patch('/usermovies/{userId}/{movieId}/rate', [UserMovieController::class, 'submitRating']);

    // Favorite Movie
    Route::patch('/usermovies/{userId}/{movieId}/favorite', [UserMovieController::class, 'toggleFavorite']);

    // ToWatch Movie
    Route::patch('/usermovies/{userId}/{movieId}/seen', [UserMovieController::class, 'toggleSeen']);

    // Settings
    // Favorite Genres
    Route::post('/settings/usergenres/{userId}', [UserGenreController::class,'update']);

    // Select Evaluator
    Route::post('/settings/evaluator/{userId}', [UserController::class,'selectEvaluator']);

    // Highest Evaluation
    Route::post('/settings/highest/{userId}/{evaluator}', [UserController::class, 'highestEvaluation']);
  });

});

