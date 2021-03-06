<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\JWTToken;
use App\Solicitud;

class SolicitudController extends Controller
{
  private $jwtToken;

  public function __construct()
  {
    $this->jwtToken = new JWTToken();
  }

  public function stadistic(Request $request)
  {
    $data = $this->jwtToken->data($request->input('token'));

    $dbResult = DB::table('solicitudes as sl')
      ->where('sl.carnet', $data->usuario->id)
      ->where('sl.ciclo', $data->ciclo)
      ->select(DB::raw('count(*) as total, sl.type'))
      ->groupBy('sl.type')
      ->get();

    $dbResultMaterias = DB::table('solicitudes as sl')
      ->where('sl.carnet', $data->usuario->id)
      ->where('sl.ciclo', $data->ciclo)
      ->select('sl.codmate')
      ->get();

    return response()->json(
      [
        'stadistic' => $dbResult,
        'materias' => $dbResultMaterias,
      ],
      200
    );
  }

  public function paginator(Request $request)
  {
    $data = $this->jwtToken->data($request->input('token'));
    $DBResult = DB::table('solicitudes as sl')
      ->join('materiaspensum as mp', 'sl.codmate', '=', 'mp.codmate')
      ->where('sl.carnet', $data->usuario->id)
      ->where('sl.ciclo', $data->ciclo)
      ->where('mp.codcarre', $data->carrera->idcarrera)
      ->orderBy('sl.created_at', 'desc')
      ->select(
        'sl.id',
        'sl.type',
        'sl.ciclo',
        'sl.carnet',
        'sl.codmate',
        'sl.estado',
        'sl.created_at',
        'mp.nommate'
      )
      ->paginate(5);

    return $DBResult;
  }

  public function index(Request $request)
  {
    $data = $this->jwtToken->data($request->input('token'));
    return json_encode(
      [
        'data' => $this->getObjectData($data->usuario->id, $data->ciclo),
      ],
      200
    );
  }

  public function add(Request $request)
  {
    $data = $this->jwtToken->data($request->input('token'));
    $carnet = $data->usuario->id;

    $type = $request->input('type');
    $observacion = $request->input('observacion');
    $sixthSubject = $request->input('sixthSubject');
    $arraySubjects = $request->input('arraySubjects');
    $codmate = $type === 'SEXTA' ? $sixthSubject['codmate'] : $request->input('subject');

    DB::beginTransaction();
    try {
      $solicitud = Solicitud::create([
        'estado' => 'I',
        'type' => $type,
        'carnet' => $carnet,
        'ciclo' => $data->ciclo,
        'codmate' => $codmate,
        'observacion' => $observacion,
        "created_at"  => date('Y-m-d'),
        "updated_at"  => date('Y-m-d'),
      ]);

      if (strcmp($type, 'SEXTA') === 0) {
        $cargaAcademica = $sixthSubject['item']['codcarga'];
        $solicitud->carga_academica()->create([
          'codcarga' => $cargaAcademica,
        ]);
      }

      if(strcmp($type, 'AGREGAR') === 0) {
        $codes = array_map(function($item) {
          return array(
            "codcarga"  => $item['codcarga']
          );
        }, $arraySubjects);
        $solicitud->carga_academica()->createMany($codes);
      }
      DB::commit();
      return response()->json(
        [
          'resolve' => true,
          "codes"   => $codes,
        ],
        200
      );
    } catch (\Throwable $th) {
      DB::rollback();
      return response()->json(
        [
          'resolve' => false,
          'message' => 'Error',
          't' => $th->getMessage(),
        ],
        402
      );
    }
  }

  private function geObjectById($id, $carrera, $type = 'SEXTA')
  {
    $dbResult = DB::table('solicitudes AS sl')
      ->join('materiaspensum AS m', 'sl.codmate', '=', 'm.codmate')
      ->where('m.codcarre', '=', $carrera)
      ->select(
        'sl.id',
        'sl.carnet',
        'sl.type',
        'sl.observacion',
        'sl.estado',
        'sl.created_at',
        'm.nommate',
        'm.codmate'
      )
      ->where('sl.id', $id)
      ->first();

    if (strcmp($type, 'SEXTA') === 0) {
      $dbResult->carga = DB::table('solicitudes_cargas_academicas as sca')
        ->join('cargaacademica as c', 'c.codcarga', '=', 'sca.codcarga')
        ->where('sca.solicitud_id', $id)
        ->select('c.turno', 'c.dias', 'c.hora', 'c.codcarga')
        ->first();
    }

    return $dbResult;
  }

  private function getObjectData($carnet, $ciclo)
  {
    $solicitud = Solicitud::where('carnet', $carnet)
      ->where('ciclo', $ciclo)
      ->with('carga_academica')
      ->get();

    return [
      'data' => $solicitud,
      'count' => count($solicitud),
    ];
  }
}
