<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header("Authorization");

        $user =  User::where("token", str_replace("Bearer ", "",$header))->first();
        
        if (!$header || !$user || $user->token_expired < time()) {
            return response()->json([
                "errors" => [
                    "messages" =>  ["unauthorized"]
                ]
                ])->setStatusCode(401);
        }

        Auth::login($user, false);
        
        return $next($request);

    }
}