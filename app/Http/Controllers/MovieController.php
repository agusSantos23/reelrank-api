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
        $orderBy = $request->input('orderBy', 'title');
        $searchTerm = $request->input('searchTerm');


        $query = Movie::query()->select([
            'movies.id',
            'movies.title',
            'movies.release_date',
            'movies.runtime',
            'movies.score',
            'movies.poster_id'
        ])->distinct();

        if ($orderBy === 'release_date' || $orderBy === 'score') {
            $query->orderBy('movies.' . $orderBy, 'desc');
        } else {
            $query->orderBy('movies.' . $orderBy);
        }

        if ($genreIdsString) {
            $genreIds = explode(',', $genreIdsString);

            $query->distinct()
                ->join('movie_genres', 'movies.id', '=', 'movie_genres.movie_id')
                ->join('genres', 'movie_genres.genre_id', '=', 'genres.id')
                ->whereIn('genres.id', $genreIds);
        }
        
        if ($searchTerm) {
            $query->where('movies.title', 'LIKE', '%' . $searchTerm . '%');
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

        Log::info('Tipo de $movies después de paginate:', [
            'type' => get_class($movies) 
        ]);
        
        Log::info('Datos de la respuesta:', $movies->toArray()); 
        

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
