<?php

namespace App\Http\Controllers\API;

use App\Alumno;
use App\JWTToken;

use App\Solicitud;
use App\StudentEnrolled;
use App\StudentEnrolledSubjects;

use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;



class AsesoriaController extends Controller
{
    private $jwtToken;
    
    public function __construct(Type $var = null) {
        $this->jwtToken = new JWTToken();
    }
    public function getEnrolledSubject(Request $request)
    {
        $ciclo = '02-2021';
        $token = $request->input('token');
        $idUser = $this->jwtToken->getId($token);
        $carreraId = $this->jwtToken->getCarrera($token);

        $objectEnrolled = $this->getObjectSubjectSchules($idUser, $carreraId, $ciclo);
        if(isset($objectEnrolled)) {
            return response()->json($objectEnrolled, 200);
        }

        return response()->json([
            "message"   => "Por el momento el alumno no posee una inscripcion activa"
        ], 402);
    }

    public function saveRegistroSubject(Request $request)
    {
        $params = $request->all();
        $idUser = $this->jwtToken->getId($params['token']);
        $carreraId = $this->jwtToken->getCarrera($params['token']);

        if(!isset($params['codCargas']))
        {
            return response()->json([
                "message" => "Debes seleccionar por lo menos una materias para poder inscribir"
            ], 402);
        }

        $find = StudentEnrolled::where('ciclo', '02-2021')
            ->where('carnet', $idUser)
            ->first();

        if(isset($find))
        {
            return response()->json([
                "message" => "El alumno ya pose una inscripcion para el ciclo actual"
            ], 402);
        }

        DB::beginTransaction();
        try {
            $cods = $params['codCargas'];
            $enrolled = StudentEnrolled::create([
                "observacion"   => "",
                "estado"        => "A",
                "carnet"        => $idUser,
                "ciclo"         => "02-2021",
            ]);

            $arrayCods = array_map(function($item){
                return new StudentEnrolledSubjects([
                    "estado"    => "D",
                    "codcarga"  => $item,
                ]);
            }, $cods);

            $enrolled->schules()->saveMany($arrayCods);
            DB::commit();
            return response()->json([
                "resolve" => true,
            ], 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                "message" => "Error",
                "t" => $th->getMessage()
            ], 402);
        }
    }

    private function getObjectSubjectSchules($carnet, $carrera, $ciclo = '02-2021')
    {
        $enrolled = DB::table('student_enrolleds')
            ->where('ciclo', $ciclo)
            ->where('carnet', $carnet)
            ->select("id", "ciclo", "carnet", "estado", "observacion")
            ->first();

            
        $subjectEnrolled = DB::table('student_enrolled_subjects as ses')
            ->join('cargaacademica as c',  "ses.codcarga", "=", "c.codcarga")
            ->join('materiaspensum as m',  "c.codmate", "=", "m.codmate")
            ->where('ses.student_enrolled_id', $enrolled->id)
            ->where('m.codcarre', $carrera)
            ->select("m.codmate", "m.nommate", "ses.estado", "c.codcarga", "c.dias", "c.hora", "c.dias", 'c.turno')
            ->get();

        return array(
            "enrolled"  => $enrolled,
            "schules"   => $subjectEnrolled,
        );
    }

    public function getHorarioSubject(Request $request)
    {
        $array = array();
        $subject = $request->input('subject');

        $schulesSubjects = DB::table('cargaacademica')
            ->where('ciclolectivo', '02-2021')
            ->where('codmate', $subject)
            ->where('tipoinscri', '1')
            ->select('turno', 'hora', 'dias', 'codcarga')
            ->get();

        $array['schules'] = $schulesSubjects;
        return response()->json($array);
    }

    private function solicitudes($carnet = '', $carrera = '01', $ciclo = '02-2021', $equal = '=')
    {
         $dbResult = DB::table('solicitudes AS sl')
            ->join('materiaspensum AS m', 'sl.codmate', '=', 'm.codmate')
            ->where('sl.carnet', '=', $carnet)
            ->where('m.codcarre', '=', $carrera)
            ->select("sl.id","sl.carnet", "sl.type", "sl.observacion", "sl.estado", "sl.created_at", "m.nommate", 'm.codmate')
            ->where('type', $equal, 'SEXTA')
            ->get();

        if(strcmp($equal, '=') === 0) {
            foreach ($dbResult as $value) {
                $value->carga = DB::table("solicitudes_cargas_academicas as sca")
                    ->join('cargaacademica as c', "c.codcarga", "=", "sca.codcarga")
                    ->where('sca.solicitud_id', $value->id)
                    ->select("c.turno", "c.dias", "c.hora", "c.codcarga")
                    ->first();
            }
        }

        return $dbResult;
    }

    public function pensum(Request $request)
    {
        $active = false;
        $enrolleds = [];
        $ciclo = '02-2021';
        $data = $this->jwtToken->data($request['token']);
        $id = $data->usuario->id;
        
        $carrera = $data->carrera->idcarrera;

        $subjects = self::subjectsToTake($data, $ciclo);
        $subjectsToTake = self::subjectSchules($subjects['take']);

        $subjectsPensum = DB::table('materiaspensum')
            ->where('codcarre', $carrera)
            ->where('plan', 'D')
            ->orderBy('nopensum')
            ->orderBy('ciclopens')
            ->select("ciclopens", "nopensum", "nommate", "codmate", "univalora", "codprere")
            ->get();

         $find = StudentEnrolled::where('ciclo', $ciclo)
            ->where('carnet', $id)
            ->first();

        if(isset($find)) {
            $active = true;
            $enrolleds = $this->getObjectSubjectSchules($id, $carrera, $ciclo);
        }

        $sextaSolicitud     = $this->solicitudes($id, $carrera, $ciclo);
        $otherSolicitudes   = $this->solicitudes($id, $carrera, $ciclo, "<>");

        return response()->json([
            "active"    => $active,
            "enrolleds" => $enrolleds,
            "pensum"    => $subjectsPensum,
            "take"      => $subjectsToTake,
            "solicitud" => [
                "sexta" => $sextaSolicitud,
                "other" => $otherSolicitudes
            ],
            "approved"  => $subjects['approved'],
            "reprobadas" => $subjects['reprobadas']
        ], 200);
    }

    public function asesoria(Request $request)
    {
        $ciclo = '02-2021';
        $data = $this->jwtToken->data($request['token']);

        $id = $data->usuario->id;
        $carrera = $data->carrera->idcarrera;

        $find = StudentEnrolled::where('ciclo', $ciclo)
            ->where('carnet', $id)
            ->first();

        if(isset($find)) {
            $objectEnrolled = $this->getObjectSubjectSchules($id, $carrera, $ciclo);
            return response()->json([
                "data"      => $objectEnrolled,
                "active"    => true,
            ], 200);
        }

        $subjects = self::subjectsToTake($data, $ciclo);
        $subjects = self::subjectSchules($subjects['take']);
        return response()->json([
            "materias"  => $subjects,
            "active"    => false
        ]);
    }

    // Materias que puede llevar
    private function subjectsToTake($data, $ciclo = '02-2021')
    {
        $id = $data->usuario->id;
        $carrera = $data->carrera->idcarrera;

        $materiasAprobadas = DB::table('cnotas as cn')
            ->join('materiaspensum as m', 'cn.codmate', '=', 'm.codmate')
            ->where('cn.carnet', $id)
            ->where('cn.estado', 'APROBADO')
            ->where('m.codcarre', $carrera)
            ->orderBy('m.ciclopens')
            ->select("m.ciclopens", "m.nopensum", "m.nommate", "m.codmate as materia")
            ->get();

        $materiasReprobadas = DB::table('cnotas as cn')
            ->join('materiaspensum as m', 'cn.codmate', '=', 'm.codmate')
            ->where('cn.carnet', $id)
            ->where('cn.estado', 'REPROBADO')
            ->where('m.codcarre', $carrera)
            ->orderBy('m.ciclopens')
            ->select("m.ciclopens", "m.nopensum", "m.nommate", "m.codmate as materia")
            ->get();

        $materiasPendientes = DB::table("materiaspensum as pensum")
            ->where('codcarre', $carrera)
            ->whereIn("codmate", function($query) use ($ciclo) {
                $query
                    ->select("codmate")
                    ->distinct()
                    ->from('cargaacademica')
                    ->where('tipoinscri', 1)
                    ->where('ciclolectivo', $ciclo);
            })
            ->select("ciclopens", "nopensum", "nommate", "codmate as materia", "prerequisi", "codprere")
            ->orderby('ciclopens')
            ->get();

        $resolve = self::resolveSubjetcs($materiasAprobadas, $materiasPendientes);
        $takeSubjects = self::resolveSubjetAsignada($resolve);

        return array(
            "take"      => $takeSubjects,
            "approved"  => $materiasAprobadas,
            'reprobadas'=> $materiasReprobadas
        );
    }

    public function subjectSchules($subjects = array())
    {
        foreach ($subjects as $key => $value) {
            $schulesSubjects = DB::table('cargaacademica')
                ->where('ciclolectivo', '02-2021')
                ->where('codmate', $value['materia'])
                ->where('tipoinscri', '1')
                ->select('turno', 'hora', 'dias', 'codcarga')
            ->get();

            $subjects[$key]['schules'] = $schulesSubjects;
        }

        return $subjects;
    }

    private function resolveSubjetcs($ganadas, $pendientes)
    {
        $array_actuales = array();
        $ganadas = self::convertToArray($ganadas);
        $pendientes = self::convertToArray($pendientes);
        
        $codigoSubject = array_map(function($item){ return $item['materia'];  }, $ganadas);
        $numPensum = array_map(function($item){ return $item['nopensum'];  }, $ganadas);

        $posiblesMaterias = array_filter($pendientes,
            function($item) use ($codigoSubject) {
                if(!in_array($item['materia'], $codigoSubject)) {
                    return $item;
                }
            }
        );

        return [
            "subjects"  => $posiblesMaterias,
            "numPensum" => $numPensum,
        ];
    }

    // (Function) -> Con esta funcion vamos a separa la materias que si se pueden llevar
    private function resolveSubjetAsignada($array = array())
    {
        $subjects = $array['subjects'];
        $numPensum = $array['numPensum'];

        $resolve = array_filter($subjects, function($item) use($numPensum) {
            $prerequisto = strtoupper($item["codprere"]);
            if(!strpos($prerequisto, "UV")) {
                if(trim($prerequisto) == "0") {
                    return $item;
                }
                if(self::verificarEquivalencias($prerequisto, $numPensum)) {
                    return $item;
                }
            }
        });

        return $resolve;
    }

    private function verificarEquivalencias($prerequisto, $materias = array() )
    {
        $explode = explode(",", $prerequisto);
        foreach ($explode as $value) {
            if(!in_array(trim($value), $materias)) {
                return false;
            }
        }
        return true;
    }

    private function convertToArray($array = array())
    {
        return json_decode(
            json_encode($array), true
        );
    }
}
