<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\Carteira;
use App\Models\Ativos;
use App\Models\Ordens;

class CarteirasController extends Controller
{
    public function index(Request $request)
    {
        $ativos = Ativos::all();
        $carteiras = User::find($request->user()->id)->carteiras()->with('ativos')->get();
        $user = User::where('id',$request->user()->id)->with('carteiras','carteirasCompartilhadas')->first();

        //dd($user);
        $data = compact('ativos','user','carteiras');
        return view('carteiras.index',$data);
    }

    public function create(Request $request){

        return view('carteiras.create');
    }


    public function show(Request $request, $id)
    {

        $ativo = User::find($request->user()->id)->carteiras()->with('ativos')->get();


        $data = compact('ativos','user');
        return view('carteiras.index',$data);
    }


    public function ordens(Request $request, $carteiraid,$ativoid)
    {
        $ativo = Ativos::where('id',$ativoid)->get();
        $carteira = Carteira::with('ordens')->where('id',$carteiraid)->where('user_id',$request->user()->id)->whereHas('ordens', function ($query) use ($ativoid){
            $query->where('ordens.ativo_id',$ativoid);
        })->get();
        $ordens = Ordens::where('ativo_id',$ativoid)->where('carteira_id',$carteiraid)->where('saldo','>',0)->get();
        $data = compact('carteira','ativo','ordens');
        //dd($data);
        return view('carteiras.ordens',$data);
    }


    public function share(Request $request, $carteira){

        $data = $request->all();

        $carteira = Carteira::where('id',$carteira)->where('user_id',$request->user()->id)
                                ->with('ativos','usuariosCompartilhados')->first();
        $data = compact('carteira');
        //dd($carteira->usuariosCompartilhados());
        return view('carteiras.compartilhar',$data);
    }

    public function sharing(Request $request){

        $data = $request->all();
        //dd($data);

        $carteira = Carteira::where('id',$data['carteiraid'])->where('user_id',$request->user()->id)
                                ->with('ativos','usuariosCompartilhados')->first();

        $user = User::where('email',$data['email'])->first();
        //dd($carteira->usuariosCompartilhados->find($user)->first());
        $usuarios = $carteira->usuariosCompartilhados()->where('user_id',$user->id)->get();
        //dd(isset($user));
        if(isset($user) && sizeof($usuarios)==0){

            //compartilha a carteira e avisa
            $carteira->usuariosCompartilhados()->attach(['user_id'=>$user->id]);
            //dd($carteira->usuariosCompartilhados());
            //dd($usuarios);
        }else{

            //envia um email pro email convidando ele a participar do sistema

        }

        //$carteiras = User::find($request->user()->id)->carteiras()->with('ativos','usuariosCompartilhados')->get();

        $data = compact('carteira');

        return redirect()->route('wallet.share.index',$carteira->id);
    }


    public function compartilharCarteira1(Request $request){

        $data = $request->all();

        $carteira = Carteira::where('id',$data['carteira'])->where('user_id',$request->user()->id)
                                ->with('ativos','usuariosCompartilhados')->first();
        $user = User::where('email',$data['email'])->first();
        if(isset($user)){

            //compartilha a carteira e avisa
            $carteira->usuariosCompartilhados()->attach(['user_id'=>$user->id]);

        }else{
            //convida o email para o sistema
        }

        $carteiras = User::find($request->user()->id)->carteiras()->with('ativos','usuariosCompartilhados')->get();

        $data = compact('carteiras');
        return view('home',$data);
    }

    public function store(Request $request){
        $data = $request->all();
        $data ['user_id'] = $request->user()->id;
        Carteira::create($data);

        return redirect()->route('wallet.index');
    }
}
