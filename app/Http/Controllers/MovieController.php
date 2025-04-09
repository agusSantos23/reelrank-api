<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovieController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 30);
        $genreIdsString = $request->input('genres');

        Log::info('Petición de películas recibida:', [
            'page' => $page,
            'limit' => $limit,
            'genres' => $genreIdsString,
            'all_request_data' => $request->all(),
        ]);

        $query = Movie::query()->select(['id', 'title', 'release_date', 'runtime', 'score', 'poster_id']);

        if ($genreIdsString) {
            $genreIds = explode(',', $genreIdsString);
            Log::info('Filtrando por géneros:', ['genre_ids' => $genreIds]);
            $query->whereHas('genres', function ($query) use ($genreIds) {
                $query->whereIn('genres.id', $genreIds);
            });
        }

        $movies = $query->paginate($limit, ['*'], 'page', $page)->map(function ($movie) {
            return [
                'id' => $movie->id,
                'title' => $movie->title,
                'releaseYear' => $movie->release_date ? (int) date('Y', strtotime($movie->release_date)) : null,
                'duration' => $movie->runtime,
                'score' => $movie->score,
                'posterId' => $movie->poster_id,
            ];
        });

        Log::info('Filtrando por géneros:', ['moviessss' => $movies]);


        return response()->json($movies);
    }


    public function show(string $id): JsonResponse
    {
        $movie = Movie::findOrFail($id)->load('genres:id,name');

        return response()->json([
            'id' => $movie->id,
            'title' => $movie->title,
            'original_title' => $movie->original_title,
            'overview' => $movie->overview,
            'original_language' => $movie->original_language,
            'score' => $movie->score,
            'release_date' => $movie->release_date,
            'budget' => $movie->budget,
            'revenue' => $movie->revenue,
            'runtime' => $movie->runtime,
            'status' => $movie->status,
            'tagline' => $movie->tagline,
            'poster_id' => $movie->poster_id,
            'backdrop_id' => $movie->backdrop_id,
            'created_at' => $movie->created_at,
            'updated_at' => $movie->updated_at,
            'genres' => $movie->genres->map(function ($genre): array {
                return ['id' => $genre->id, 'name' => $genre->name];
            }),
        ]);
    }
}
