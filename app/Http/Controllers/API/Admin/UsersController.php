<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\User;
use App\perfil;
use App\JWTToken;
use App\usuario_perfil;

class UsersController extends Controller
{
    private $jwtToken;
    public function __construct()
    {
        $this->jwtToken = new JWTToken();
    }

    public function darBajarUser(Request $request)
    {
        $id = $request->input('id');
        $estado = $request->input('estado');

        try {
            $usuario = usuario_perfil::find($id);    
            $usuario->estado = $estado;
            $usuario->save();
            return response()->json([
                'ok'    => true,
                'data'  => $usuario,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "ok"        => false,
                'line'      => $th->getLine(),
                'msg'       => $th->getMessage(),
            ], 405);
        }
    }

    public function password(Request $request)
    {
        $usuario    = $request->input('usuario');
        $password   = $request->input('password');

        if($usuario && $password) {
            $password_hash = base64_encode(hash('sha224', $password));
            $user = DB::table('usuarios')
                ->where('iduser', $usuario)
                ->first();

            if($user) {
                DB::table('usuarios')
                    ->where('iduser', $usuario)
                    ->update([
                        'passweb'   => $password_hash
                    ]);

                return response()->json([
                    'ok'    => true
                ]);
            }
        }

        return response()->json([
            "ok"        => false,
            'message'   => 'Lo datos ingresados no son validos'
        ], 405);
    }

    public function perfil(Request $request)
    {
        $perfil     = $request->input('perfil');
        $usuario    = $request->input('usuario');

        if($usuario && $perfil) {
            $user = DB::table('usuario_perfils')
                ->where('usuario_id', $usuario)
                ->first();

            if($user) {
                DB::table('usuario_perfils')
                    ->where('usuario_id', $usuario)
                    ->update([
                        'perfil_id'   => $perfil
                    ]);

                $perfil = DB::table('perfils')
                    ->where('id', $perfil)
                    ->select('id', 'nombre')
                    ->first();

                return response()->json([
                    'ok'        => true,
                    "perfils"    => $perfil
                ]);
            }
        }

        return response()->json([
            "ok"        => false,
            'message'   => 'Lo datos ingresados no son validos'
        ], 405);
    }

    public function nameLastChange(Request $request)
    {
        $nombres    = $request->input('nombres');
        $usuario    = $request->input('usuario');
        $apellidos  = $request->input('apellidos');
        
        if($nombres && $usuario && $apellidos) {
            $user = DB::table('usuarios')
                ->where('iduser', $usuario)
                ->first();

            if($user) {
                DB::table('usuarios')
                    ->where('iduser', $usuario)
                    ->update([
                        'nomuser'   => $nombres,
                        'apeuser'   => $apellidos
                    ]);

                return response()->json([
                    'ok'        => true
                ]);
            }
        }

        return response()->json([
            "ok"        => false,
            'message'   => 'Lo datos ingresados no son validos'
        ], 405);

    }

    public function all(Request $request)
    {
        $search = $request->input('search');

        $dbResult = DB::table('usuario_perfils as up')
            ->join('usuarios as us', 'up.usuario_id', '=', 'us.iduser')
            ->join('perfils as p', 'p.id', '=', 'up.perfil_id');

        if(strcmp($search, '') !== 0) {
            $dbResult
                ->where('us.nomuser', 'like', "%$search%")
                ->orWhere('us.apeuser', 'like', "%$search%")
                ->orWhere('us.iduser', 'like', "%$search%");
        }
            
            
        $dbResult = $dbResult->orderBy('p.nombre', 'ASC')
            ->select(
                'up.usuario_id', 'up.perfil_id', 'up.estado', 'us.nomuser', 'us.apeuser',
                'p.nombre', 'up.id as user_perfil', 'up.id'
            )
            ->paginate(5);

        return response()->json([
            "paginate"  => $dbResult,
            "perfiles"  => perfil::select('id as value', 'nombre as title')->where('is_admin', 1)->get(),
        ]);
    }

    public function save(Request $request)
    {
        $user = $request->input('user');
        DB::beginTransaction();

        try {
            $id = DB::table('usuarios')->insertGetId(
                array(
                    'pass'              => '',
                    'nivel'             => 1,
                    'rolsistema'        => 4,
                    'iduser'            => $user['username'],
                    'nomuser'           => $user['nombres'],
                    'apeuser'           => $user['apellidos'],
                    'passweb'          => base64_encode(hash('sha224', $user['password'])),
                )
            );

            usuario_perfil::create([
                'usuario_id'    => $user['username'],
                'perfil_id'     => $user['perfil'],
                'estado'        => 1
            ]);

            DB::commit();
            $usuario = DB::table('usuario_perfils as up')
                ->join('usuarios as us', 'up.usuario_id', '=', 'us.iduser')
                ->join('perfils as p', 'p.id', '=', 'up.perfil_id')
                ->where('us.iduser', $user['username'])
                ->select(
                    'up.usuario_id', 'up.perfil_id', 'up.estado', 'us.nomuser', 'us.apeuser',
                    'p.nombre', 'up.id as user_perfil', 'up.id'
                )
                ->first();
            return response()->json([
                'ok'    => true,
                'data'   => $usuario,
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                "ok"        => false,
                'line'      => $th->getLine(),
                'msg'       => $th->getMessage(),
            ], 405);
        }
        
    }

    public function validateUsername(Request $request)
    {
        $username = $request->input('username');
        $user = DB::table('usuarios')
            ->where('iduser', $username)
            ->first();

        if($user === null) {
            return response()->json([
                'ok' => true
            ]);
        }

        return response()->json([
            'ok'    => false
        ], 405);
    }
}
