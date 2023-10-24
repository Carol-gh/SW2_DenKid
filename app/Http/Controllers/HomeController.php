<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


use App\Models\Infante;
use App\Models\Personal;
use App\Models\EvaluacionDenver;
use App\Models\Area;
use App\Models\Estado;
use App\Models\Evaluacion_Estado;
use App\Models\ResultadoDenver;
use Carbon\Carbon;
use App\Models\Pregunta;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $rol = DB::table('model_has_roles')->where('model_id', auth()->user()->id)->get()->first();
        $rolP = DB::table('roles')->where('name', 'Padre')->get()->first();
        $infante = DB::table('infante')->where('userId', auth()->user()->id)->get()->first();
        if ($rol->role_id == $rolP->id) {
            $evaluacion = DB::table('evaluaciondenver')->where('infanteId', $infante->id)->orderBy('created_at', 'desc')->first();
            $personal = DB::table('personal')->where('id',  $evaluacion->personalId)->get()->first();

        $result = ResultadoDenver::where('evaluacionId',$evaluacion->id)->where('denverescalaId', 2)->get();
        $MG = Pregunta::whereIn('id', $result->pluck('preguntaId'))
        ->where('areaId', 1)
        ->get();

        $MF = Pregunta::whereIn('id', $result->pluck('preguntaId'))
        ->where('areaId', 2)
        ->get();
        $AL =  Pregunta::whereIn('id', $result->pluck('preguntaId'))
        ->where('areaId', 3)
        ->get();
        $PS =  Pregunta::whereIn('id', $result->pluck('preguntaId'))
        ->where('areaId', 4)
        ->get();

        $estado = Estado::all();
        $totalFilas = $estado->count();
        $data = [];

        foreach ($estado as $estadoItem) {
            $evaluacionesEstado = Evaluacion_Estado::where('estado_id', $estadoItem->id)
                ->where('evaluacion_id', $evaluacion->id)
                ->count();
        
            $emocion = $estadoItem->name;
        
            $data[$emocion] = $evaluacionesEstado;
        }        
            return view('evaluaciones.resultados', compact('evaluacion','personal','infante','data'),compact('MG','MF','AL','PS','totalFilas'));
        } else {
            return view('home');

        }


    }
}

