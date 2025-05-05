<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

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
}
