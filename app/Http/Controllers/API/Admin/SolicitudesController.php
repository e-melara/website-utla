<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\JWTToken;
use App\Solicitud;
use App\SolicitudesCargasAcademica;

class SolicitudesController extends Controller {
    private $jwtToken;
    public function __construct()
    {
        $this->jwtToken = new JWTToken();
    }

    public function save(Request $request)
    {
        $id = $request->input('id');
        $rows   = $request->input('rows');
        $type   = $request->input('type');
        $status = $request->input('status');

        $solicitud = Solicitud::find($id);

        DB::beginTransaction();
        try {
            // cambiando el estado de la solicitud
            $solicitud->estado = $status;
            $solicitud->save();

            // Verificando si el tipo de solicitud es sexta o una agregacion para asi ver la cargas y hacer los cambios
            if($type === 'SEXTA' || $type === 'AGREGAR') {
                SolicitudesCargasAcademica::where('solicitud_id', $id)->update([
                    'estado'    => 'D'
                ]);

                if($status === 'A') {
                    SolicitudesCargasAcademica::whereIn('id', $rows)->update([
                        "estado"    => 'A'
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                "ok"        => true, 
                "id"        => $id,
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                "ok"        => true,
                'line'      => $th->getLine(),
                'msg'       => $th->getMessage(),
            ]);
        }
    }

    public function all(Request $request)
    {
        $type   = $request->input('type');
        $search = trim($request->input('search'));

        $dbResult = DB::table('solicitudes as sl')
        ->join('alumnos as a', 'a.carnet', '=', 'sl.carnet')
        ->join('carreras as c', 'c.idcarrera', '=', 'a.idcarrera')
        ->where('sl.ciclo', '02-2021')
        ->where('sl.estado', ($type === '2' ? 'I' : 'A'));

        if(strcmp($search, '') !== 0) {
            $dbResult
                ->where('sl.carnet', 'like', "%$search%")
                ->orWhere('a.apellidos', 'like', "%$search%")
                ->orWhere('a.nombres', 'like', "%$search%");
        }

        $dbResult = $dbResult
            ->orderBy('sl.updated_at', 'ASC')
            ->select(
                "sl.id",
                "sl.carnet",
                "sl.type",
                "sl.updated_at",
                "a.nombres",
                "a.apellidos",
                "sl.estado",
                "c.nomcarrera",
            )
        ->paginate(10);

        return response()->json($dbResult);
    }

    public function findById(Request $request, $id)
    {
        $solicitud = Solicitud::find($id);
        if($solicitud->type === "SUFICIENCIA" || $solicitud->type === "TUTORIADA") {
            return $this->solicitudDataSuficienciaOTutoriada($solicitud);
        }
        return $this->solicitudSextoOAddicion($solicitud);
    }

    // functions private suficiencia o tutoriada
    private function solicitudDataSuficienciaOTutoriada($solicitud)
    {
        $data = DB::table('materiaspensum as mp')
            ->join("carreras as c", "c.idcarrera", "=", "mp.codcarre")
            ->join("alumnos as a", "c.idcarrera", "=", "a.idcarrera")
            ->where('mp.codmate', $solicitud->codmate)
            ->where('a.carnet', $solicitud->carnet)
            ->select(
                "mp.ciclopens",
                "mp.nommate",
                "mp.codmate",
                "c.nomcarrera",
                "a.apellidos",
                "a.nombres",
                "a.carnet"
            )
            ->first();
        return response()->json([
            "data"          => $data,
            "solicitud"     => $solicitud
        ]);
    }

    // functions private sexta o addicion
    private function solicitudSextoOAddicion($solicitud)
    {
        $data = DB::table('alumnos as a')
            ->join("carreras as c", "c.idcarrera", "=", "a.idcarrera")
            ->where('a.carnet', $solicitud->carnet)
            ->select(
                "c.idcarrera",
                "c.nomcarrera",
                "a.apellidos",
                "a.nombres",
                "a.carnet"
            )
            ->first();

        $cargas = $solicitud->carga_academica;
        foreach ($cargas as $value) {
            $value['carga'] = DB::table('cargaacademica as c')
                ->join("materiaspensum as mp", 'c.codmate', '=', 'mp.codmate')
                ->where('mp.codcarre', $data->idcarrera)
                ->where('c.codcarga', $value->codcarga)
                ->select(
                    "c.hora", "c.dias", "c.turno", "mp.nommate", "mp.ciclopens", "c.codcarga"
                )->first();
        }

        return response()->json([
            "data"      => $data,
            "solicitud"     => $solicitud
        ]);
    }
}
