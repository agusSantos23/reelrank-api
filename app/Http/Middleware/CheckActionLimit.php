<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CheckActionLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->route('userId');

        if (!$userId) return response()->json(['message' => 'User ID not provided in route'], 400);
        
        $user = User::find($userId);

        if (!$user) return response()->json(['message' => 'User not found'], 404);
        

        if ($user->status === 'blocked') {
            return response()->json(['message' => 'Your account is temporarily blocked.Try it again in a minute.'], 429);
        }


        $now = Carbon::now();
        $lastActionAt = Carbon::parse($user->last_action_at ?? $now->subSeconds(30));

        $timeDifference = abs($now->diffInSeconds($lastActionAt));

        if ($timeDifference> 20) {
            $user->action_count = 0;
        } elseif ($timeDifference < 5) {
            Log::debug("enter ++");
            $user->increment('action_count');
        }

        $user->last_action_at = $now;

        if ($user->action_count >= 10) $user->status = 'blocked';
        
        $user->save();

        if ($user->status === 'blocked') {
            return response()->json(['message' => 
                'You have performed too many actions. Your account has been temporarily blocked for a minute.'
                
            ], 429);

        }

        return $next($request);
    }
}