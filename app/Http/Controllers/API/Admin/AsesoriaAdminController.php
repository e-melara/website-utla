<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

use App\JWTToken;
use App\StudentEnrolled;
use App\StudentEnrolledSubjects;

// Model para los pagos
use App\Pago;
use App\Banco;

class AsesoriaAdminController extends Controller
{
  private $jwtToken;

  public function __construct()
  {
    $this->jwtToken = new JWTToken();
  }
  public function changeStatus(Request $request)
  {
    DB::beginTransaction();
    try {
      $id = $request->input('id');
      $type = $request->input('type');
      $data = $request->input('data');

      StudentEnrolledSubjects::where('student_enrolled_id', $id)->update([
        'estado' => 'D',
      ]);

      StudentEnrolledSubjects::whereIn('id', $data)->update([
        'estado' => 'A',
      ]);
      StudentEnrolled::find($id)->update([
        'estado' => $type === 'ACEPTADA' ? 'V' : 'P',
      ]);
      DB::commit();
      return response()->json([
        'validated' => true,
      ]);
    } catch (\Throwable $th) {
      DB::rollBack();
      return response()->json([
        'validated' => false,
        't' => $th->getMessage(),
      ]);
    }
  }

  public function all(Request $request)
  {
    $type   = $request->input('type');
    $status = trim($request->input('estado'));
    $search = trim($request->input('search'));

    $dbResult = DB::table('student_enrolleds as se')
      ->join('alumnos as al', 'al.carnet', '=', 'se.carnet')
      ->join('carreras as cr', 'cr.idcarrera', '=', 'al.idcarrera')
      ->where('se.ciclo', '02-2021');

    if(strcmp($status, '') === 0) {
      if(strcmp($type, 2) === 0) {
        $dbResult->where('se.estado', '<>', 'F');
      }else{
        $dbResult->whereIn('se.estado', array('V', 'F'));
      }
    }else {
      $dbResult->where('se.estado', $status);
    }

    if (strcmp($search, '') !== 0) {
      $dbResult
        ->where('se.carnet', 'like', "%$search%")
        ->orWhere('al.apellidos', 'like', "%$search%")
        ->orWhere('al.nombres', 'like', "%$search%");
    }

    $dbResult = $dbResult
      ->orderBy('se.created_at', 'ASC')
      ->select(
        'se.id',
        'se.carnet',
        'se.created_at',
        'al.apellidos',
        'al.nombres',
        'cr.nomcarrera',
        'se.estado'
      )
      ->paginate(10);

    return response()->json($dbResult);
  }

  public function getById(Request $request, $id)
  {
    $output = new \Symfony\Component\Console\Output\ConsoleOutput();
    $enrolled = StudentEnrolled::with('schules')->find($id);

    if ($enrolled) {
      $carnet = $enrolled->carnet;
      $this->getSubjectEquivalate($enrolled, $carnet);
      if(strcmp($enrolled->estado, 'F') === 0) {
        $pagos = Pago::where('student_enrolled_id', $id)
            ->with('aranceles', 'archivos', 'banco')
          ->first();

        foreach ($pagos->aranceles as $value) {
          $resolve = DB::table('aranceles')
              ->where('idarancel', $value->arancel_id)
              ->select('descripcion')
            ->first();
          $value['descripcion'] = $resolve->descripcion;
        }

        $enrolled['pago'] = $pagos;
      }
      return response()->json([
        'enrolled' => $enrolled,
      ]);
    }

    return response()->json([
        'message' => 'Solicitud no encontrada',
      ], 404);
  }

  public function aranceles(Request $request)
  {
    try {
      $tokenData = $this->jwtToken->data($request['token']);
      $carnet = $tokenData->usuario->id;
      $student = DB::table('alumnos as al')
        ->join('carreras as c', 'al.idcarrera', '=', 'c.idcarrera')
        ->where('al.carnet', $carnet)
        ->select('c.codigo', 'al.cuota')
        ->first();

      $codigo = $student->codigo;
      $codigoCuota = $codigo . '0101';
      $codigoAranceles = $codigo . '0201%';

      $aranceles = DB::table('aranceles as ara')
        ->where('idarancel', 'like', $codigoAranceles)
        ->where('idpadre', '<>', 0)
        ->Orwhere('idarancel', $codigoCuota)
        ->select('ara.idarancel', 'ara.precio', 'ara.descripcion')
        ->get();

      $bancos = Banco::select('id as value', 'is_referido', 'nombre as title')->get();

      return response()->json([
        'resolve' => true,
        'data' => [
          'student' => $student,
          'bancos'    => $bancos,
          'aranceles' => $aranceles,
        ],
      ]);
    } catch (\Throwable $th) {
      return response()->json([
        'resolve' => false,
        't' => $th->getMessage(),
      ]);
    }
  }

  /**
   * Metodo para guardar la informacion del pago
   * @parameter input('pago') -> la informacion del pago
  */
  public function pagos(Request $request)
  {
    // validaciones
    $this->validate($request, [
      "pago"      => 'required',
      "aranceles" => 'required',
      "files"     => 'required',
      "files.*"   => 'mimes:pdf,jpg,png'
    ]);

    $carnet = $this->jwtToken->getId($request['token']);
    $asesoria = StudentEnrolled::where('carnet', $carnet)->first();

    $pago = json_decode($request->input('pago'), true);
    $aranceles = json_decode($request->input('aranceles'), true);

    try {
      DB::beginTransaction();
      // guardado los datos del pago
      $pagoDB = Pago::create([
        "is_titular"          => 1,
        "student_enrolled_id" => $asesoria->id,
        "banco_id"            => $pago['banco'],
        "monto"               => $pago['monto'],
        "concepto"            => $pago['concepto'],
        "nombre_titular"      => $pago['referido'],
        "fecha_pago"          => date('Y-m-d', strtotime($pago['fechaPago'])),
      ]);

      // guardado los aranceles del pago
      $arancelesArray = array();
      foreach ($aranceles as $value) {
        $arancelesArray[] = [
          "arancel_id"    => $value['id'],
          "precio"        => $value['precio']
        ];
      }

      // Guardado los datos de los aranceles
      $pagoDB->aranceles()->createMany( $arancelesArray );

      // subiendos los archivos
      $dataFiles = array();
      if($request->hasFile('files')) {
        $files = $request->file('files');
        foreach ($files as $file) {
          $name = $file->store('files');
          $extension = $file->extension();
          $dataFiles[] = [
            "url"     => $name,
            "tipo"    => $extension
          ];
        }
      }

      // Guardado los datos de los archivos
      $pagoDB->archivos()->createMany( $dataFiles );
      $asesoria->estado = 'F';
      $asesoria->save();

      DB::commit();
      return response()->json([
        "ok"    => true,
        "pago"  => $pagoDB->toData()
      ]);
    } catch (\Throwable $th) {
      DB::rollBack();
      return response()->json([
        'ok'  => false,
        "t"   => $th->getMessage(),
        "line" => $th->getLine()
      ], 403);
    }
  }

  // methods private
  private function getSubjectEquivalate($enrolled, $carnet)
  {
    $cargas = $enrolled->schules;
    $student = DB::table('alumnos as al')
      ->where('al.carnet', $carnet)
      ->select('al.idcarrera')
      ->first();

    foreach ($cargas as $key => $value) {
      $subject = DB::table('cargaacademica as cg')
        ->join('materiaspensum as mt', 'cg.codmate', '=', 'mt.codmate')
        ->where('cg.codcarga', $value->codcarga)
        ->where('mt.codcarre', $student->idcarrera)
        ->select('cg.hora', 'cg.codmate', 'cg.dias', 'mt.nommate', 'mt.codprere')
        ->first();
      $subject->estado = $value->estado;
      if ($subject->codprere !== '0') {
        $subject->prerequisito = $this->getRequisito(
          $subject->codprere,
          $student->idcarrera,
          $carnet
        );
      }

      $cargas[$key]['subjects'] = $subject;
    }
  }

  private function getRequisito($prerequito, $carrera, $carnet)
  {
    if (strpos($prerequito, ',')) {
      $aResponse = [];
      $explode = explode($prerequito, ',');
      foreach ($explode as $value) {
        $aResponse[] = $this->getOneById($value, $carrera, $carnet);
      }
      return $aResponse;
    } else {
      return $this->getOneById($prerequito, $carrera, $carnet);
    }
  }

  private function getOneById($requisito, $carrera, $carnet)
  {
    return DB::table('materiaspensum as mp')
      ->join('cnotas as c', 'mp.codmate', '=', 'c.codmate')
      ->where('c.carnet', $carnet)
      ->where('mp.codcarre', $carrera)
      ->where('mp.nopensum', $requisito)
      ->select('c.promedio', 'mp.codmate', 'mp.nommate', 'mp.nopensum')
      ->get();
  }
}
