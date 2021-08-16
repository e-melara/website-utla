<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * El horario no depende de las tablas de asesoria sino de las cnotas
*/
class NotesController extends Controller
{   private $jwtToken;
    
    public function __construct(Type $var = null) {
        $this->jwtToken = new JWTToken();
    }
    
    public function me(Request $request)
    {
        $ciclo = '02-2021';
        $data = $this->jwtToken->data($request->input('token'));
        $array = array("active" => false, 'history' => array(), 'schules' => array());

        $history = DB::table('cnotas as cn')
            ->join("materiaspensum as m" , " cn.codmate", "=", "m.codmate")
            ->where('cn.carnet', $data->usuario->id)
            ->where('m.codcarre', $data->carrera->idcarrera)
            ->where('ciclolectivo', '<>', $ciclo)
            ->select("cn.ciclolectivo", "cn.estado", "cn.promedio", "m.codmate", "m.nommate", "m.ciclopens")
        ->get();

        $schules = DB::table('student_enrolleds as se')
            ->join("student_enrolled_subjects as ses", " se.id", "=", "ses.student_enrolled_id")
            ->join("cargaacademica as c", "ses.codcarga", "=", "c.codcarga")
            ->join("materiaspensum as m ", "c.codmate", "=", "m.codmate")
            ->where("se.carnet", $data->usuario->id)
            ->where('se.ciclo', $ciclo)
            ->where('m.codcarrera', $data->carrera->idcarrera)
            ->select("se.estado", "ses.codcarga", "c.hora", "c.dias", "ciclolectivo", "c.codmate", "m.nommate")
        ->get();


        if($schules) {
            $array['active'] = true;
            $array['schules'] = $schules;
        }

        $array['history'] = $history;
        return response()->json($array, 200);
    }
}
