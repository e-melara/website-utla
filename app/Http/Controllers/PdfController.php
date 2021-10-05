<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\PDF;
use App\Pago;
use App\JWTToken;

class PdfController extends Controller
{
    private $jwtToken;
    public function __construct()
    {
        $this->jwtToken = new JWTToken();
    }

    public function all(Request $request)
    {
        $end   = $request->input('end');
        $begin = $request->input('begin');
        $token = $request->input('token');
        
        if($begin && $end && $token) {
            $data = $this->jwtToken->data($token);
            // creado el object para el pdf
            $pdf = new PDF('L', 'mm', array(215.9, 167.0));
            $pdf->SetAutoPageBreak(false);
            // configurando los marges
            $pdf->SetMargins(4, 5);
            // Grosor de las lineas
            $pdf->SetLineWidth(0.5);

            $students = DB::table('student_enrolleds as se')
                ->join('alumnos as a', "se.carnet", "=", "a.carnet")
                ->join('carreras as c', "a.idcarrera", "=", "c.idcarrera")
                ->where('se.estado', 'M')
                ->where('se.ciclo', $data->ciclo)
                ->whereBetween("se.updated_at", array($begin, $end))
                ->select(
                    "se.id", "se.ciclo", "se.carnet", "se.updated_at as created",
                    "a.nombres", "a.apellidos", "c.nomcarrera", "c.idcarrera"
                )->get();


            if(isset($students)) {
                foreach ($students as $value) {
                    $positionY = $pdf->GetY();
                    $pdf->AddPage();
                    $subjects = DB::table('student_enrolled_subjects as ses')
                        ->join("cargaacademica as c", "c.codcarga", "=", "ses.codcarga")
                        ->join("materiaspensum as m", "c.codmate", "=", "m.codmate")
                        ->where("ses.student_enrolled_id", $value->id)
                        ->where('ses.estado', 'A')
                        ->where('m.codcarre', $value->idcarrera)
                        ->select(
                            "c.codmate", "c.turno", "c.aula", "c.dias", "c.hora", "c.lab",
                            "c.tipoinscri", "m.nommate"
                        )
                        ->orderBy('c.lab', 'DESC')
                        ->orderBy('c.dias')
                        ->get();
                    $pdf->hojaInscripcion($value, $subjects, 6);
                }
                $pdf->Output();
                exit;
            }
        }
        return response()->json([
            "message"   => "La peticion no puede ser realizada con exito"
        ], 404);
    }

    public function matricula(Request $request, $id)
    {
        $token = $request->input('token');
        
        if($token) {
            // creado el object para el pdf
            $pdf = new PDF();
            $pdf->SetAutoPageBreak(false);
            // configurando los marges
            $pdf->SetMargins(4, 5);
            // agregando la pagina inicial
            $pdf->AddPage();
            // Grosor de las lineas
            $pdf->SetLineWidth(0.5);
            // posicion "Y" del documento
            $positionY = $pdf->GetY();
    
            $student = DB::table('student_enrolleds as se')
                ->join('alumnos as a', "se.carnet", "=", "a.carnet")
                ->join('carreras as c', "a.idcarrera", "=", "c.idcarrera")
                ->where('se.carnet', $id)
                ->where('se.estado', 'M')
                ->where('se.ciclo', '02-2021')
                ->select(
                    "se.id", "se.ciclo", "se.carnet", "se.created_at as created",
                    "a.nombres", "a.apellidos", "c.nomcarrera", "c.idcarrera"
                )->first();
    
            if(isset($student)) {
                $subjects = DB::table('student_enrolled_subjects as ses')
                    ->join("cargaacademica as c", "c.codcarga", "=", "ses.codcarga")
                    ->join("materiaspensum as m", "c.codmate", "=", "m.codmate")
                    ->where("ses.student_enrolled_id", $student->id)
                    ->where('ses.estado', 'A')
                    ->where('m.codcarre', $student->idcarrera)
                    ->select(
                        "c.codmate", "c.turno", "c.aula", "c.dias", "c.hora", "c.lab",
                        "c.tipoinscri", "m.nommate"
                    )
                    ->orderBy('c.lab', 'DESC')
                    ->orderBy('c.dias')
                    ->get();
                // Output Hoja de Inscripcion
                $pdf->hojaInscripcion($student, $subjects, $positionY);
                // Output file
                $pdf->Output();
                exit;
            }
        }
    }

    public function pago(Request $request, $id)
    {
        $token = $request->input('token');
        
        // creado el object para el pdf
        $pdf = new PDF();
        // configurando los marges
        $pdf->SetMargins(4, 5);
        // agregando la pagina inicial
        $pdf->AddPage();
        // Grosor de las lineas
        $pdf->SetLineWidth(0.5);
        // posicion "Y" del documento
        $positionY = $pdf->GetY();

        $student = DB::table('student_enrolleds as se')
            ->join('alumnos as a', "se.carnet", "=", "a.carnet")
            ->join('carreras as c', "a.idcarrera", "=", "c.idcarrera")
            ->where('se.carnet', $id)
            ->where('se.ciclo', '02-2021')
            ->select(
                "se.id", "se.ciclo", "se.carnet", "se.created_at as created",
                "a.nombres", "a.apellidos", "c.nomcarrera", "c.idcarrera"
            )->first();

        if(isset($student)) {
            $subjects = DB::table('student_enrolled_subjects as ses')
                ->join("cargaacademica as c", "c.codcarga", "=", "ses.codcarga")
                ->join("materiaspensum as m", "c.codmate", "=", "m.codmate")
                ->where("ses.student_enrolled_id", $student->id)
                ->where('ses.estado', 'A')
                ->where('m.codcarre', $student->idcarrera)
                ->select(
                    "c.codmate", "c.turno", "c.aula", "c.dias", "c.hora", "c.lab",
                    "c.tipoinscri", "m.nommate"
                )
                ->orderBy('c.lab', 'DESC')
                ->orderBy('c.dias')
                ->get();

            // object pago
            $pago = Pago::where('student_enrolled_id', $student->id)->first();
            $pdf->hojaPago($student, $subjects, $pago, $positionY);
            $pdf->Output();
            exit;
        }
    }
}
