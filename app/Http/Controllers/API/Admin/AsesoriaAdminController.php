<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

use App\StudentEnrolled;
use App\StudentEnrolledSubjects;

class AsesoriaAdminController extends Controller
{
    public function changeStatus(Request $request)
    {
        DB::beginTransaction();
        try {
            $id     = $request->input('id');
            $type   = $request->input('type');
            $data   = $request->input('data');

            StudentEnrolledSubjects::where('student_enrolled_id', $id)
                ->update([
                    'estado' => 'D'
                ]);

            StudentEnrolledSubjects::whereIn('id', $data)
                ->update([
                    'estado' => 'A'
                ]);
            StudentEnrolled::find($id)->update([
                'estado'    => ($type === 'ACEPTADA') ? 'V' : 'P'
            ]);
            DB::commit();
            return response()->json([
                'validated' => true,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'validated' => false,
                "t" => $th->getMessage()
            ]);
        }

    }

    public function all(Request $request)
    {
        $status = $request->input('estado');
        $search = trim($request->input('search'));

        $dbResult = DB::table('student_enrolleds as se')
            ->join('alumnos as al', 'al.carnet', '=', 'se.carnet')
            ->join('carreras as cr', 'cr.idcarrera', '=', 'al.idcarrera')
            ->where('se.ciclo', '02-2021');

        if(strcmp($search, '') !== 0) {
            $dbResult->where('se.carnet', 'like', "%$search%")
                ->orWhere('al.apellidos', 'like', "%$search%")
                ->orWhere('al.nombres', 'like', "%$search%");
        }else {
            $dbResult->where('se.estado', $status);
        }

        $dbResult = $dbResult
            ->orderBy('se.created_at', 'DESC')
            ->select('se.id', 'se.carnet', 'se.created_at', 'al.apellidos', 'al.nombres', 'cr.nomcarrera', 'se.estado')
            ->paginate(10);

        return response()->json($dbResult);
    }

    public function getById(Request $request, $id)
    {
        $enrolled = StudentEnrolled::with('schules')->find($id);

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
            $subject->estado = $value->estado;
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
            ->get();
    }
}