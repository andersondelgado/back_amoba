<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Client as Oauth;

class PersonalAccessToken
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $clientID = env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID');
        $secret = env('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET');

        if (!$request->hasHeader('X-PERSONAL_ID') && !$request->hasHeader('X-PERSONAL_ID')) {
            return response()->json(['message' => 'Unauthenticated!.'], 401);
        } else if (
            strcmp($request->header('X-PERSONAL_ID'), $clientID) != 0
            ||
            strcmp($request->header('X-PERSONAL_SECRET'), $secret) != 0) {
            return response()->json(['message' => 'Unauthenticated!!.'], 401);
        } else {
            $count = Oauth::where('id', $clientID)
                ->
                where('secret', $secret)
                ->count();
            if ($count <= 0) {
                return response()->json(['message' => 'Unauthenticated!!!.'], 401);
            } else {
                return $next($request);
            }
        }
    }
}
