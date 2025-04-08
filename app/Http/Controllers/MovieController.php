<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 30); 

        $movies = Movie::paginate($limit, ['id', 'title', 'release_date', 'runtime', 'score', 'poster_id'], 'page', $page)
            ->map(function ($movie) {
                return [
                    'id' => $movie->id,
                    'title' => $movie->title,
                    'releaseYear' => $movie->release_date ? (int) date('Y', strtotime($movie->release_date)) : null,
                    'duration' => $movie->runtime,
                    'score' => $movie->score,
                    'posterId' => $movie->poster_id,
                ];
            });

        return response()->json($movies);
    }
}
