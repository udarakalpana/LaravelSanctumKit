<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && !$request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Email verification required',
                'errors' => null,
            ], 403);
        }

        return $next($request);
    }
}
