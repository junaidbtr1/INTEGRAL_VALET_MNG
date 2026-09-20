<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBuildingOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->isBuildingOwner() && ! $user->isSuperAdmin())) {
            return response()->json([
                'success' => false,
                'message' => 'Building owner access required.',
                'error_code' => 'AUTH_004',
            ], 403);
        }

        return $next($request);
    }
}
