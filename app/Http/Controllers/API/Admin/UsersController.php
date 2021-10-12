<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\JWTToken;

class UsersController extends Controller
{
    private $jwtToken;
    public function __construct()
    {
        $this->jwtToken = new JWTToken();
    }

    public function all(Request $request)
    {
        $dbResult = DB::table('usuario_perfils as up')
            ->join('usuarios as us', 'up.usuario_id', '=', 'us.iduser')
            ->join('perfils as p', 'p.id', '=', 'up.perfil_id')
            ->orderBy('p.nombre', 'ASC')
            ->select(
                'up.usuario_id', 'up.perfil_id', 'up.estado', 'us.nomuser', 'us.apeuser',
                'p.nombre', 'up.id as user_perfil'
            )
            ->paginate(5);
        
        return response()->json($dbResult);
    }
}
