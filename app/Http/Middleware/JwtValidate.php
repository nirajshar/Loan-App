<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JwtValidate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ( $e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException ) {
                return response()->json(['status' => 403, 'message' => 'Token is Invalid', 'error' => 'FORBIDDEN'], 403);
            } else if ( $e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException ) {
                return response()->json(['status' => 403, 'message' => 'Token is Expired', 'error' => 'FORBIDDEN'], 403);
            } else {
                return response()->json(['status' => 403, 'message' => 'Authorization Token not found', 'error' => 'FORBIDDEN'], 403);
            }
        }
        return $next($request);
    }
}
