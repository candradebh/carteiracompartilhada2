<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ativos;
use App\Models\Corretoras;
use App\Models\Operacoes;
use App\Models\Ordens;
use App\Services\ExecutarOrdem;
use App\User;
use Illuminate\Support\Facades\DB;

class OperacoesController extends Controller
{
    public function index(Request $request){

        $operacoesSplit = Operacoes::whereIn('tipooperacao',['inplit','split'])->with('ativo')->get();
        $operacoesFusao = Operacoes::whereIn('tipooperacao',['fusao'])->with('ativo')->get();
        $data = compact('operacoesSplit','operacoesFusao');
        //dd($data);
        return view('operacoes.index',$data);
    }
    public function create(Request $request){

        $ativos = Ativos::orderBy('nome','asc')->get();
        $data = compact('ativos');

        return view('operacoes.create',$data);
    }

    public function store(Request $request){
        $data = $request->all();
        return redirect()->route('operacoes.index');
    }
}
