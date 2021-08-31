<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class EventosController extends Controller
{
    public function index(Request $request)
    {
        $params = $request->input('date');
        $sqlQuery  = "SELECT ev.begin_date, ev.title, ev.end_date, is_end_date, ev.month_year,";
        $sqlQuery .= "DATEDIFF(ev.end_date, current_date) as dias FROM eventos as ev ";
        $sqlQuery .= "WHERE month_year ";
        
        if(isset($params)) {
            $sqlQuery .= "= '$params';";
        }else {
            $sqlQuery .= "like concat('%', MONTH(current_date), '-', year(current_date));";
        }
        $DbResult =  DB::select ($sqlQuery) ;
        return response()->json([
            "data"  => $DbResult, 
            "query" => $sqlQuery,
        ]);
    }

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
        //
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
