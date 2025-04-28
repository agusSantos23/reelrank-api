<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\User;
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

        return response()->json($movies);
    }

    public function show(string $movieId, ?string $userId = null): JsonResponse
    {
        $movie = Movie::with('genres:id,name')->findOrFail($movieId);
        $userRelation = null;
    
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $userMovie = $user->movies()
                    ->where('movie_id', $movieId)
                    ->withPivot([
                        'is_favorite', 
                        'seen', 
                        'rating', 
                        'story_rating', 
                        'acting_rating', 
                        'visuals_rating', 
                        'music_rating', 
                        'entertainment_rating', 
                        'pacing_rating'
                    ])
                    ->first();
    
    
                if (!$userMovie) {
                    $user->movies()->attach($movieId);
                    $userMovie = $user->movies()->where('movie_id', $movieId)->first();
                }
    
                $userRelation = $userMovie ? $userMovie->pivot->toArray() : null;

                if ($userRelation && isset($userRelation['seen'])) {
                    $userRelation['seen'] = (bool) $userRelation['seen'];
                }
            }
        }
    
        $responseData = $movie->toArray();
        $responseData['genres'] = $movie->genres->map(fn ($genre) => ['id' => $genre->id, 'name' => $genre->name]);
        $responseData['user_relation'] = $userRelation;
    
        return response()->json($responseData);
    }
}
