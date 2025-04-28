<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        $updated = $user->movies()
            ->where('movie_id', $movieId)
            ->updateExistingPivot($movieId, [$columnToUpdate => $ratingValue]);

        if ($updated) {
            return response()->json(['message' => 'Recorded/updated score correctly']);
        } else {
            $user->movies()->attach($movieId, [$columnToUpdate => $ratingValue]);
            return response()->json(['message' => 'Recorded score properly']);
        }
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
                    true => 'Movie marked as watched',
                    false => 'Movie marked as seen',
                };
                return response()->json(['message' => $message]);
            } else {

                return response()->json(['message' => 'Seen status not changed']);
            }
        } else {

            $user->movies()->attach($movieId, ['seen' => $seen]);
            $message = match ($seen) {
                null => 'Seen status set to unclassified',
                true => 'Movie marked as watched',
                false => 'Movie marked as seen',
            };
            return response()->json(['message' => $message]);
        }
    }
}
