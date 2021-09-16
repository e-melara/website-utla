<?php

namespace App\Http\Controllers\API;

use App\JWTToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

/**
 * El horario no depende de las tablas de asesoria sino de las cnotas
 */
class NotesController extends Controller
{
  private $jwtToken;

  public function __construct(Type $var = null)
  {
    $this->jwtToken = new JWTToken();
  }

  public function me(Request $request)
  {
    $ciclo = '02-2021';
    $data = $this->jwtToken->data($request->input('token'));
    $array = [
      'active' => false,
      'history' => [],
      'schules' => [],
      'loading' => true,
    ];

    $history = DB::table('cnotas as cn')
      ->join('materiaspensum as mp', 'mp.codmate', '=', 'cn.codmate')
      ->where('cn.carnet', $data->usuario->id)
      ->where('mp.codcarre', $data->carrera->idcarrera)
      ->where('cn.ciclolectivo', '<>', $ciclo)
      ->select(
        'cn.ciclolectivo',
        'cn.estado',
        'cn.promedio',
        'mp.codmate',
        'mp.nommate',
        'mp.ciclopens'
      )
      ->get();

    $schules = DB::table('student_enrolleds as se')
      ->join('student_enrolled_subjects as ses', 'se.id', '=', 'ses.student_enrolled_id')
      ->join('cargaacademica as c', 'ses.codcarga', '=', 'c.codcarga')
      ->join('materiaspensum as mp', 'c.codmate', '=', 'mp.codmate')
      ->where('se.carnet', $data->usuario->id)
      ->where('se.ciclo', $ciclo)
      ->where('mp.codcarre', $data->carrera->idcarrera)
      ->select(
        'se.estado',
        'ses.codcarga',
        'c.hora',
        'c.dias',
        'c.ciclolectivo',
        'c.codmate',
        'mp.nommate'
      )
      ->get();

    if ($schules) {
      $array['active'] = true;
      $array['schules'] = $schules;
    }

    $array['history'] = $history;
    return response()->json($array, 200);
  }
}
