<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Carteira;
use App\Models\Ativos;
use App\Models\Corretoras;
use App\Models\Ordens;
use App\Services\ExecutarOrdem;
use App\User;
use Illuminate\Support\Facades\DB;

class OrdensController extends Controller
{
    public function index(Request $request){

        $carteiras = User::find($request->user()->id)->carteiras()->with('ordens')->get();
        $data = compact('carteiras');
        //dd($data);
        return view('ordens.index',$data);
    }
    public function create(Request $request){

        $ativos = Ativos::orderBy('nome','asc')->get();
        $corretoras= Corretoras::where('realizaimportacao',1)->get();//'Xp Investimentos'
        $carteiras = User::find($request->user()->id)->carteiras()->get();

        $data = compact('ativos','carteiras','corretoras');

        return view('ordens.create',$data);
    }
    public function show(Request $request){

        $params = $request->all();
        //dd($params);

        $ano = $params["ano"];
        $mes = $params["mes"];
        $tipo = $params["tipo"];

        //carteiras do usuario
        $carteiras = DB::table("carteiras")->select("id")->where("user_id", $request->user()->id)->get();

        if(isset($ano) && isset($mes) && isset($tipo)){

            $ordens = Ordens::whereYear('data', '=', $ano)
                            ->whereMonth('data', '=', $mes)
                            ->whereHas('ativo', function($query) use ($tipo){
                                $query->where("categoria","like", $tipo);
                            })->get();

        }else{

            $ordens = Ordens::whereIn('carteira_id',$carteiras)->get();

        }


        $data = compact('ordens');
        //dd($data);
        return view('ordens.show',$data);
    }

    public function store(Request $request){
        $data = $request->all();
        $data['total'] = $data['quantidade'] * $data['preco'] - $data['despesas'] - ($data['outras_despesas']!=null? $data['outras_despesas']:0.0);
        $executar = new ExecutarOrdem($data);
        $executar->executar();
        //dd($executar);

        return redirect()->route('wallet.index');
    }
}
