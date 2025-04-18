<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    
    public function show(string $id): JsonResponse
    {
        $user = User::with('avatar')->find($id);

        if (!$user) return response()->json(['message' => 'User not found'], 404);
        

        return response()->json($user);
    }
}
