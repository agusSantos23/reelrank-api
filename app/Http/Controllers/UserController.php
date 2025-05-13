<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request; 
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function show(string $userId): JsonResponse
    {
        $user = User::with(['avatar', 'genres:id,name'])->find($userId);

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

    public function userStatistics(string $userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        
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

    
    public function selectEvaluator(Request $request, string $userId){

        $validator = Validator::make($request->all(), [
            'value' => 'required|string|in:starts,slider'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::findOrFail($userId);
        $user->config_scorer = $request->input('value');
        $user->save();

        return response()->json(['message' => 'Properly updated evaluation configuration']);
    }


    public function highestEvaluation(Request $request, string $userId, string $evaluator)
    {
        $validator = Validator::make($request->all(), [
            'max' => 'required|integer|min:1', 
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::findOrFail($userId);

        if ($evaluator === 'starts') {
            $user->maximum_star_rating = $request->input('max');

        } elseif ($evaluator === 'slider') {
            $user->maximum_slider_rating = $request->input('max');

        } else {
            return response()->json(['message' => 'Evaluator not valid'], 400);

        }

        $user->save();

        return response()->json(['message' => 'Maximum updated evaluation value correctly']);
    }
}
