<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Carteira;
use App\Models\Ativos;
use App\Models\Corretoras;
use App\Models\Ordens;
use App\Services\ExecutarOrdem;
use App\User;

class CambioController extends Controller
{
    public function index(Request $request){

        $exchanges = User::find($request->user()->id)->cambios()->get();
        $data = compact('exchanges');
        //dd($data);
        return view('exchange.index',$data);
    }
    public function create(Request $request){

        $ativos = Ativos::orderBy('nome','asc')->get();
        $corretoras= Corretoras::where('cambio',1)->get();
        $carteiras = User::find($request->user()->id)->carteiras()->get();

        $data = compact('ativos','carteiras','corretoras');

        return view('exchange.create',$data);
    }

    public function store(Request $request){
        $data = $request->all();

        dd($data);

        return redirect()->route('exchange.index');
    }
}
