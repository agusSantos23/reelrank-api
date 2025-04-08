<?php

use App\Http\Controllers\GenreController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;




Route::resource('/movies', MovieController::class);

Route::resource('/genres', GenreController::class);
