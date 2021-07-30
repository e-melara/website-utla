<?php

namespace App\Http\Middleware;

use Closure;
use App\JWTToken;
use Firebase\JWT\JWT;

class CheckValidToken
{
    public function handle($request, Closure $next)
    {
        $jwtToken = new JWTToken();
        $authorization = $request->header('Authorization');
        $token = trim(str_replace("bearer ", "", $authorization));

        if(!$token)
            return response()->json([ "message" => "No token" ], 401);

        try {
            $jwtToken->check($token);
        } catch (\Throwable $t) {
            return response()->json([ 
                "message" => "Token invalido"
            ], 401);
        }

        $request['token'] = $token;
        return $next($request);
    }
}
