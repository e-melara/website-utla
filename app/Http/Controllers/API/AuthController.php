<?php
namespace App\Http\Controllers\API;

use App\User;
use App\JWTToken;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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

        $array = array();
        $array['usuario']  = [
            "id"        => $user->iduser,
            "nombres"   => $user->nomuser,
            "apellidos" => $user->apeuser,
            "nivel"     => $user->nivel,
        ];

        if($user->nivel === '3') {
            $array['carrera'] = DB::table('alumnos')
                ->join('carreras', 'alumnos.idcarrera', '=', 'carreras.idcarrera')
                ->where('alumnos.carnet', $user->iduser)
                ->select('carreras.idcarrera', 'carreras.nomcarrera')
                ->first();
        }

        $array['token'] = $this->jwtToken->signIn($array);
        return response()->json($array);
    }

    public function me(Request $request)
    {
        $token = $request['token'];
        $data = $this->jwtToken->data($token);
        return response()->json($data);
    }
}