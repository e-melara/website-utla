<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\StudentEnrolled;

class AsesoriaAdminController extends Controller
{
    public function all(Request $request)
    {
        $dbResult = DB::table('student_enrolleds as se')
            ->join('alumnos as al', 'al.carnet', '=', 'se.carnet')
            ->join('carreras as cr', 'cr.idcarrera', '=', 'al.idcarrera')
            ->where('se.estado', 'A')
            ->where('se.ciclo', '02-2021')
            ->orderBy('se.created_at', 'DESC')
            ->select('se.id', 'se.carnet', 'se.created_at', 'al.apellidos', 'al.nombres', 'cr.nomcarrera')
            ->paginate(10);

        return response()->json($dbResult);
    }

    public function getById(Request $request, $id)
    {
        $enrolled = StudentEnrolled::where('estado', 'A')->with('schules')->find($id);

        if($enrolled) {
            $carnet = $enrolled->carnet;
            $this->getSubjectEquivalate($enrolled, $carnet);
            return response()->json([
                "enrolled"  => $enrolled,
            ]);
        }

        return response()->json([
            "message" => 'Solicitud no encontrada',
        ], 404);
    }

    private function getSubjectEquivalate($enrolled, $carnet)
    {
        $cargas = $enrolled->schules;
        $student = DB::table('alumnos as al')
            ->where('al.carnet', $carnet)
            ->select('al.idcarrera')
            ->first();

        foreach ($cargas as $key => $value) {
            $subject = DB::table('cargaacademica as cg')
                ->join("materiaspensum as mt", "cg.codmate", "=", "mt.codmate")
                ->where('cg.codcarga', $value->codcarga)
                ->where('mt.codcarre', $student->idcarrera)
                ->select("cg.hora", "cg.codmate", "cg.dias", "mt.nommate", "mt.codprere")
                ->first();

            if($subject->codprere !== '0') {
                $subject->prerequisito = $this->getRequisito($subject->codprere, $student->idcarrera, $carnet);
            }

            $cargas[$key]['subjects'] = $subject;
        }
    }

    private function getRequisito($prerequito, $carrera, $carnet)
    {
        if(strpos($prerequito, ',')) {
            $aResponse = array();
            $explode = explode($prerequito, ',');
            foreach ($explode as $value) {
                $aResponse[] = $this->getOneById($value, $carrera, $carnet);
            }
            return $aResponse;
        }else {
            return $this->getOneById($prerequito, $carrera, $carnet);
        }
    }

    private function getOneById($requisito, $carrera, $carnet)
    {
        return DB::table('materiaspensum as mp')
            ->join("cnotas as c","mp.codmate","=","c.codmate")
            ->where('c.carnet', $carnet)
            ->where('mp.codcarre', $carrera)
            ->where('mp.nopensum', $requisito)
            ->select("c.promedio", "mp.codmate", "mp.nommate", "mp.nopensum")
            ->first();
    }
}