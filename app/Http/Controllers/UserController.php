<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    public function show(string $id): JsonResponse
    {
        $user = User::with('avatar')->find($id);

        if (!$user) return response()->json(['message' => 'User not found'], 404);


        return response()->json($user);
    }

    public function unblock(string $userId)
    {
        $user = User::find($userId);

        if (!$user) return response()->json(['message' => 'User not found'], 404);


        if ($user->status === 'blocked') {

            $user->action_count = 0;
            $user->status = 'normal';
            $user->save();

            return response()->json($user);
        } else {
            return response()->json(['message' => 'The account is not blocked.'], 400);
        }
    }

    public function userStatistics(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        
        $mostViewedGenre = DB::table('user_movies')
            ->where('user_id', $user->id)
            ->whereNotNull('rating')
            ->join('movies', 'user_movies.movie_id', '=', 'movies.id')
            ->join('movie_genres', 'movies.id', '=', 'movie_genres.movie_id')
            ->join('genres', 'movie_genres.genre_id', '=', 'genres.id')
            ->select('genres.name')
            ->groupBy('genres.name')
            ->orderByDesc(DB::raw('count(*)'))
            ->pluck('genres.name')
            ->first();


        $averageRating = DB::table('user_movies')
            ->where('user_id', $user->id)
            ->whereNotNull('rating')
            ->avg('rating') ?? 0;

        $watchingMovies = DB::table('user_movies')
            ->where('user_id', $user->id)
            ->where('seen', true)
            ->join('movies', 'user_movies.movie_id', '=', 'movies.id')
            ->sum('movies.runtime') ?? 0;

        $statistics = [
            'most_viewed_genre' => $mostViewedGenre,
            'rated_movies' => $user->ratedMovies->count(),
            'watching_movies' => intval($watchingMovies),
            'average_rating' => round($averageRating, 2),
        ];

        return response()->json($statistics);
    }
}
