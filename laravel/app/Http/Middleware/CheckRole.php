<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    private const ROLE_LEVELS = [
        'Client' => 1,
        'Manager' => 2,
        'Admin' => 3,
    ];

    public function handle(Request $request, Closure $next, string $requiredRole): Response
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $userLevel = self::ROLE_LEVELS[$user->role] ?? 0;
        $requiredLevel = self::ROLE_LEVELS[$requiredRole] ?? 99;

        if ($userLevel < $requiredLevel) {
            return response()->json([
                'error' => "Forbidden: requires {$requiredRole} role or higher",
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}