<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class RoleValidate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return $this->unauthorized('Token expired');
        } catch (TokenInvalidException $e) {
            return $this->unauthorized('Token invalid');
        } catch (JWTException $e) {
            return $this->unauthorized('Authorization token not found');
        }

        if ($user && in_array($user->role, $roles)) {
            return $next($request);
        }
    
        return $this->unauthorized();
    }

    private function unauthorized($message = null){
        return response()->json([
            'status' => 403,
            'message' => $message ? $message : 'Forbidden Resource for Role',
            'error' => 'FORBIDDEN'
        ], 403);
    }
}
