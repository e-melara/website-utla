<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\perfil;
use App\JWTToken;
use App\perfil_modulo;

class PerfilesController extends Controller
{
    private $jwtToken;
    public function __construct()
    {
        $this->jwtToken = new JWTToken();
    }

    public function findById(Request $request, $id)
    {
       $perfil = perfil::find($id);
       if($perfil) {
           $dataModulos = DB::table('modulos as m')
            ->get();

           $modulos = DB::table('perfil_modulos as pm')
                ->join('modulos as m', 'pm.modulo_id', '=', 'm.id' )
                ->where('pm.perfil_id', $id)
                ->select('pm.*', 'm.nombre', 'm.icon')
                ->get();

            $usuarios = DB::table('usuario_perfils as up')
                ->join('usuarios as u', 'up.usuario_id', '=', 'u.iduser')
                ->where('up.perfil_id', $id)
                ->select('up.*', 'u.nomuser', 'u.apeuser')
                ->get();
            
            return response()->json([
                "data"  => [
                    "perfil"    => $perfil,
                    "modulos"   => $modulos,
                    "usuarios"  => $usuarios,
                    "dataModulos"   => $dataModulos,
                ],
                'ok'    => true
            ]);
       }

       return response()->json([
           "ok" => false,
       ], 404);
    }

    public function all(Request $request)
    {
        $all = DB::table('perfils')->get();

        foreach ($all as $value) {
            $value->modulos = DB::table('perfil_modulos as pm')
                ->join('modulos as m', 'pm.modulo_id', '=', 'm.id')
                ->where('pm.perfil_id', $value->id)
                ->select('m.nombre')
                ->get();
        }
        return response()->json([
            'all'   => $all
        ]);
    }

    public function eliminarModulo(Request $request)
    {
        $id = $request->input('id');

        DB::beginTransaction();
        try {
            $moduloPerfil = perfil_modulo::where('id', $id)->delete();
            DB::commit();
            return response()->json([
                "ok"        => true, 
                "id"        => $id,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                "ok"        => true,
                'line'      => $th->getLine(),
                'msg'       => $th->getMessage(),
            ], 406);
        }
    }

    public function add(Request $request)
    {
        $id = $request->input('id');
        $modulo = $request->input('modulo');

        if($id && $modulo) {
            $object = perfil_modulo::create([
                "perfil_id" => $id,
                "modulo_id" => $modulo
            ]);

            $modulo = DB::table('perfil_modulos as pm')
                ->join('modulos as m', 'pm.modulo_id', '=', 'm.id' )
                ->where('pm.modulo_id', $modulo)
                ->select('pm.*', 'm.nombre', 'm.icon')
                ->first();
            
            return response()->json([
                "data"    => $modulo,
            ]);
        }

        return response()->json([
            "ok"    => false,
        ], 405);
    }

    public function newPerfil(Request $request)
    {
        $nombre = $request->input('nombre');
        if($nombre) {
            $perfil = perfil::create([
                "nombre"        => $nombre,
                'is_admin'      => 1, 
                'is_student'    => 0
            ]);

            $perfil->modulos = [];

            return response()->json([
                "perfil"    => $perfil
            ]);
        }

        return response()->json([
            "ok"    => false,
        ], 405);
    }

    public function updatePerfil(Request $request)
    {
        $id = $request->input('id');
        $nombre = $request->input('nombre');

        if($nombre && $id) {
            DB::beginTransaction();
            try {
                perfil::where('id', $id)
                    ->update([
                        "nombre"    => $nombre
                    ]);
                DB::commit();
                $perfil = perfil::where('id', $id)->first();
                return response()->json([
                    "perfil"    => $perfil
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json([
                    "ok"        => false,
                    'line'      => $th->getLine(),
                    'msg'       => $th->getMessage(),
                ], 406);
            }
        }

        return response()->json([
            "ok"    => false,
        ], 405);
    }
}
