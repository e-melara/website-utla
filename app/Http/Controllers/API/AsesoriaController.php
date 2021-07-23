<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\Alumno;


class AsesoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = Alumno::where('carnet', $id)
            ->select('carnet', 'apellidos', 'nombres', 'idcarrera as carrera')
            ->first();

        $materiasAprobadas = DB::table('cnotas as cn')
            ->join('materiaspensum as m', 'cn.codmate', '=', 'm.codmate')
            ->where('cn.carnet', $id)
            ->where('cn.estado', 'APROBADO')
            ->where('m.codcarre', $item['carrera'])
            ->orderBy('m.ciclopens')
            ->select("m.ciclopens", "m.nopensum", "m.nommate", "m.codmate as materia")
            ->get();

        $materiasPendientes = DB::table("materiaspensum as pensum")
            ->where('codcarre', $item['carrera'])
            ->whereIn("codmate", function($query) {
                $query
                    ->select("codmate")
                    ->distinct()
                    ->from('cargaacademica')
                    ->where('tipoinscri', 1)
                    ->where('ciclolectivo', '02-2021'); // obtener ciclo de la base de datos
            })
            ->select("ciclopens", "nopensum", "nommate", "codmate as materia", "prerequisi", "codprere")
            ->orderby('ciclopens')
            ->get();

        $resolve = self::resolveSubjetcs($materiasAprobadas, $materiasPendientes);
        self::resolveSubjetAsignada($resolve);
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
        
        dd($resolve);
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
