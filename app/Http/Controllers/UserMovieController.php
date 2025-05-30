<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;


class UserMovieController extends Controller
{

    public function index(Request $request, string $userId): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 30);
        $searchTerm = $request->input('searchTerm');
        $listType = $request->input('list');

        $query = Movie::query()->select([
            'movies.id',
            'movies.title',
            'movies.release_date',
            'movies.runtime',
            'user_movies.rating as user_rating',
            'movies.poster_id'
        ])->distinct();

        $query->join('user_movies', 'movies.id', '=', 'user_movies.movie_id')
            ->where('user_movies.user_id', $userId);

        if ($listType === 'favorite') {
            $query->where('user_movies.is_favorite', true);
        } elseif ($listType === 'seen') {
            $query->where('user_movies.seen', true);
        } elseif ($listType === 'see') {
            $query->where('user_movies.seen', false);
        }


        $query->orderBy('user_movies.rating', 'desc');


        if ($searchTerm) {
            $query->where('movies.title', 'LIKE', '%' . $searchTerm . '%');
        }

        $movies = $query->paginate($limit, ['*'], 'page', $page)->map(function ($movie) {
            return [
                'id' => $movie->id,
                'title' => $movie->title,
                'releaseYear' => $movie->release_date ? (int) date('Y', strtotime($movie->release_date)) : null,
                'duration' => $movie->runtime,
                'score' => $movie->user_rating,
                'posterId' => $movie->poster_id,
            ];
        });

        return response()->json($movies);
    }

    public function submitRating(Request $request, string $userId, string $movieId): JsonResponse
    {
        $rules = [
            'value' => 'required|integer|min:0|max:100',
            'column' => 'required|string|in:rating',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $columnToUpdate = $request->input('column');
        $ratingValue = $request->input('value');

        $user = User::findOrFail($userId);


        $updated = $user->movies()
            ->where('movie_id', $movieId)
            ->updateExistingPivot($movieId, [$columnToUpdate => $ratingValue]);


        if (!$updated) {
            $user->movies()->attach($movieId, [$columnToUpdate => $ratingValue]);
        }

        $randomNumber = rand(1, 10);

        if ($randomNumber < 4) {

            $averageRating = DB::table('user_movies')
                ->where('movie_id', $movieId)
                ->avg('rating');


            Movie::where('id', $movieId)->update(['score' => $averageRating]);
        }

        return response()->json(['message' => 'Score recorded correctly']);
    }

    public function toggleFavorite(Request $request, string $userId, string $movieId): JsonResponse
    {

        $validator = Validator::make($request->all(), ['value' => 'required|boolean']);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $isFavorite = $request->input('value');

        $user = User::findOrFail($userId);

        $updated = $user->movies()
            ->where('movie_id', $movieId)
            ->updateExistingPivot($movieId, ['is_favorite' => $isFavorite]);

        if ($updated) {
            return response()->json(['message' => 'Favorite status updated successfully']);
        } else {
            $user->movies()->attach($movieId, ['is_favorite' => $isFavorite]);
            return response()->json(['message' => 'Favorite status set successfully']);
        }
    }

    public function toggleSeen(Request $request, string $userId, string $movieId): JsonResponse
    {
        $validator = Validator::make($request->all(), ['value' => 'nullable|boolean']);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $seen = $request->input('value');
        $user = User::findOrFail($userId);

        if ($user->movies()->where('movie_id', $movieId)->exists()) {

            $updated = $user->movies()->updateExistingPivot($movieId, ['seen' => $seen]);

            if ($updated) {
                $message = match ($seen) {
                    null => 'Seen status set to unclassified',
                    true => 'Movie marked as seen',
                    false => 'Movie marked as see',
                };
                return response()->json(['message' => $message]);
            } else {

                return response()->json(['message' => 'Seen status not changed']);
            }
        } else {

            $user->movies()->attach($movieId, ['seen' => $seen]);
            $message = match ($seen) {
                null => 'Seen status set to unclassified',
                true => 'Movie marked as seen',
                false => 'Movie marked as see',
            };
            return response()->json(['message' => $message]);
        }
    }
}
