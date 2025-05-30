<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Http\Request;

class UserGenreController extends Controller
{
    
    public function index(?string $userId = null)
    {
        $user = null;

        if ($userId) $user = User::find($userId);
        
        $genres = Genre::select('id', 'name')->get();

        $listGenres = $genres->map(function ($genre) use ($user) {
            $isActive = false;

            if ($user) {
                $isActive = $user->genres()
                    ->where('genres.id', $genre->id)
                    ->exists();
            }

            return [
                'id' => $genre->id,
                'name' => $genre->name,
                'active' => $isActive,
            ];
        });

        return response()->json($listGenres);
    }


    public function update(Request $request, string $userId)
    {
        $user = User::findOrFail($userId);

        $request->validate([
            'genre_ids' => 'array',
            'genre_ids.*' => 'uuid|exists:genres,id',
        ]);

        $genreIdsToSync = $request->input('genre_ids');

        $user->genres()->sync($genreIdsToSync);

        return response()->json(['message' => 'User genres updated correctly']);
    }

}
