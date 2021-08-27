<?php

namespace App\Http\Controllers\API;

use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\JWTToken;
use App\Solicitud;
use App\StudentEnrolled;
use App\StudentEnrolledSubjects;
use App\SolicitudesCargasAcademica;



class SolicitudController extends Controller
{
    private $jwtToken;
    
    public function __construct(Type $var = null) {
        $this->jwtToken = new JWTToken();
    }

    public function index(Request $request)
    {
        $data = $this->jwtToken->data($request->input('token'));
        return json_encode([
            "data" => $this->getObjectData($data->usuario->id) 
        ], 200);
    }

    public function add(Request $request)
    {
        $data = $this->jwtToken->data($request->input('token'));
        $carnet      = $data->usuario->id;
        $type        = $request->input('type');
        $object      = $request->input('object');
        $observacion = $request->input('observacion');
        $sixthSubject= $request->input('sixthSubject');
        $codmate = ($type === 'SEXTA') ? $sixthSubject['codmate'] : $request->input('subject');

        DB::beginTransaction();
        try {
            $solicitud = Solicitud::create([
                "estado"        => 'I',
                "type"          => $type,
                "carnet"        => $carnet,
                "codmate"       => $codmate,
                "ciclo"         => '02-2021',
                "observacion"   => $observacion,
            ]);

            if(strcmp($type, "SEXTA") === 0) {
                $cargaAcademica = $sixthSubject['item']['codcarga'];
                $solicitud->carga_academica()->create([
                    "codcarga"  => $cargaAcademica
                ]);
            }
            DB::commit();
            return response()->json([
                "resolve" => true
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                "resolve" => false,
                "message" => "Error",
                "t" => $th->getMessage()
            ], 402);
        }
    }

    private function geObjectById($id, $carrera, $type = 'SEXTA')
    {
        $dbResult = DB::table('solicitudes AS sl')
            ->join('materiaspensum AS m', 'sl.codmate', '=', 'm.codmate')
            ->where('m.codcarre', '=', $carrera)
            ->select("sl.id","sl.carnet", "sl.type", "sl.observacion", "sl.estado", "sl.created_at", "m.nommate", 'm.codmate')
            ->where('sl.id', $id)
            ->first();

        if(strcmp($type, "SEXTA") === 0) {
            $dbResult->carga = DB::table("solicitudes_cargas_academicas as sca")
                    ->join('cargaacademica as c', "c.codcarga", "=", "sca.codcarga")
                    ->where('sca.solicitud_id', $id)
                    ->select("c.turno", "c.dias", "c.hora", "c.codcarga")
                ->first();
        }

        return $dbResult;
    }

    private function getObjectData($carnet)
    {
        $solicitud =  Solicitud::where('carnet', $carnet)
            ->where('ciclo', '02-2021')
            ->with('carga_academica')
            ->get();

        return array(
            "data"  => $solicitud,
            "count" => count($solicitud),
        );
    }
}