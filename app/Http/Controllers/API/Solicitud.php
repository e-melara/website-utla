<?php

namespace App\Http\Controllers\API;

use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\JWTToken;
use App\StudentEnrolled;
use App\StudentEnrolledSubjects;



class Solicitud extends Controller
{
    private $jwtToken;
    
    public function __construct(Type $var = null) {
        $this->jwtToken = new JWTToken();
    }

    public function sexta(Request $request)
    {
        $data = $this->jwtToken->data($request->input('token'));
    }

    public function tutoriada(Request $request)
    {
        $data = $this->jwtToken->data($request->input('token'));
    }

    public function suficiencia(Request $request)
    {
        $data = $this->jwtToken->data($request->input('token'));
    }
}
