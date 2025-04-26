<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserMovieController extends Controller
{
    public function submitRating(Request $request, string $userId, string $movieId): JsonResponse
    {
        $allowedRatingColumns = [
            'rating',
            'story_rating',
            'acting_rating',
            'visuals_rating',
            'music_rating',
            'entertainment_rating',
            'pacing_rating',
        ];

        $rules = [
            'value' => 'required|integer|min:0|max:100',
            'column' => 'required|string|in:' . implode(',', $allowedRatingColumns),
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $columnToUpdate = $request->input('column');
        $ratingValue = $request->input('value');

        $user = User::findOrFail($userId);

        $updated = $user->movies()->where('movie_id', $movieId)->updateExistingPivot($movieId, [$columnToUpdate => $ratingValue]);

        if ($updated) {
            return response()->json(['message' => 'Recorded/updated score correctly']);
        } else {
            $user->movies()->attach($movieId, [$columnToUpdate => $ratingValue]);
            return response()->json(['message' => 'Recorded score properly']);
        }
    }
}
