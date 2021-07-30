<?php

namespace App\Http\Controllers\API;


use App\User;
use App\JWTToken;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private $jwtToken;
    
    public function __construct(Type $var = null) {
        $this->jwtToken = new JWTToken();
    }
    
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "username"  => "required",
            "password"  => "required",
        ], [
            "required"  => 'El campo :attribute es requerido',
        ]);

        if($validator->fails()) {
            return response()->json([
                "validator"   => true,
                "errors"    => $validator->messages()
            ], 401);
        }

        $password_hash =  base64_encode(hash("sha224", $request->password));
        $user = User::where('iduser', $request->username)
                ->where('passweb', $password_hash)
                ->select('iduser', 'nomuser', 'apeuser', 'nivel')
                ->first();

        if(!$user) {
            return response()->json([
                "message"   => "El usuario o contraseña no es correcta"
            ], 401);
        }

        $token = $this->jwtToken->signIn([
            "usuario" => array(
                "nombres"   => $user->nomuser,
                "apellidos" => $user->apeuser
            )
        ]);
        
        return response()->json([
            "token"     => $token,
            "usuario"   => [
                "nombres"   => $user->nomuser,
                "apellidos" => $user->apeuser
            ]
        ]);
    }

    public function logout() {}

    public function refresh(){}

    public function me(Request $request)
    {
        $token = $request['token'];
        $data = $this->jwtToken->data($token);
        return response()->json($data);
    }
}