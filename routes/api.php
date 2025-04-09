<?php

use App\Http\Controllers\GenreController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;



// Movie
Route::resource('/movies', MovieController::class);

Route::get('/movies/{movie}', [MovieController::class, 'show']);


// Genre
Route::resource('/genres', GenreController::class);
