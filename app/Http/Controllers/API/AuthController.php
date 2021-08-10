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
            ], 403);
        }

        $password_hash =  base64_encode(hash("sha224", $request->password));
        $user = User::where('iduser', $request->username)
                ->where('passweb', $password_hash)
                ->select('iduser', 'nomuser', 'apeuser', 'nivel')
                ->first();

        if(!$user) {
            return response()->json([
                "message"   => "El usuario o contraseña no es correcta"
            ], 403);
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
            $array['rol'] = $this->getRoleUser(true);
        }else {
            $result = $this->getRoleUser(false, $user->iduser);
            if(!$result['result']) {
                return response()->json([
                    "message"   => "El usuario no ha sido encontrado"
                ], 403);
            }
            $array['rol'] = $result;
        }

        $array['token'] = $this->jwtToken->signIn($array);
        return response()->json($array);
    }

    public function getRoleUser($isStudent = false, $idUser = null)
    {
        if($isStudent) {
            $DBPerfilStudent = DB::table('perfils')
                ->where('is_student', 1)
                ->select('nombre', 'id')
                ->first();

            $DBModulesStudent = $this->getModulesPerfil($DBPerfilStudent->id);

            return array(
                "perfil" => $DBPerfilStudent->nombre,
                "routes" => $DBModulesStudent,
                "rol"    => 'IS_STUDENT'
            );
        }else{
            $DBPerfilRolAdmin= DB::table('usuario_perfils as up')
                ->join('perfils as p', 'p.id', '=', 'up.perfil_id')
                ->where('up.usuario_id', $idUser)
                ->select('p.nombre', 'p.id')
                ->first();

            if($DBPerfilRolAdmin) {
                $DBModulesAdminRole = $this->getModulesPerfil($DBPerfilRolAdmin->id);
                return array(
                    "result"    => true,
                    "perfil"    => $DBPerfilRolAdmin->nombre,
                    "routes"    => $DBModulesAdminRole,
                    "rol"    => 'IS_ADMIN'
                );
            }

            return array(
                "result"    => false,
            );
        }
    }

    private function getModulesPerfil($idPerfil)
    {
        return Db::table('perfil_modulos as pm')
            ->join('modulos as m', 'm.id', '=', 'pm.modulo_id')
            ->where('perfil_id', $idPerfil)
            ->select('m.*', 'pm.add', 'pm.view', 'pm.delete', 'pm.update')
            ->get();
    }

    public function me(Request $request)
    {
        $token = $request['token'];
        $data = $this->jwtToken->data($token);
        return response()->json($data);
    }
}