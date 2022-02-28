<?php

namespace App\Http\Controllers;

use App\Models\CarteiraAnualIrpf;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ordens;
use App\Models\Resultados;
use Carbon\Carbon;
use Inertia\Inertia;

class ImpostosController extends Controller
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


    public function index(Request $request)
    {

        $carteiras = User::find(auth()->user()->id)->carteiras()->get();

        $anosComOrdens = Ordens::whereHas('carteira', function ($query) use ($request){
                            $query->where('carteiras.user_id',$request->user()->id);
                        })->get()
                        ->groupBy(function($val) {
                            return Carbon::parse($val->data)->format('Y');
                        });
        $anos = [];
        foreach($anosComOrdens as $key=>$value){
            $anos[] = ['ano'=>$key,'ordens'=>$value];
        }

        $buscaAno = $request->get('ano');
        if($buscaAno==null){
            $buscaAno = date('Y');
            $ano = $buscaAno;
        }
        $posicaoAnual = CarteiraAnualIrpf::with('ativo')->where('user_id',auth()->user()->id)->where('ano',$buscaAno)->get();
        $resultadosAcoes = Resultados::where('tipoativo','Ações')->where('ano',$buscaAno)->where('user_id',auth()->user()->id)->orderBy('mes','asc')->get();
        $resultadosFii = Resultados::where('tipoativo','Fundos Imobiliários')->where('ano',$buscaAno)->where('user_id',auth()->user()->id)->orderBy('mes','asc')->get();
        $data = compact('buscaAno','anos','resultadosAcoes','resultadosFii','carteiras','posicaoAnual','ano');

        return Inertia::render('Relatorios/Irpf/Index', $data);

        //return view('relatorios.index',$data);
    }


    public function gerarDarf(Request $request,$darf)
    {
        $resultado = Resultados::with('user')->where('id', $darf)->first();

        $darfGerada = null;

        if($resultado!=null && $resultado->darf > 10){

            //definindo a data limite para pagar
            $mes = $resultado->mes+1;
            $ultimoDia = date("t", mktime(0,0,0, $mes ,'01', $resultado->ano));
            $dataStr = $resultado->ano."-".($mes<10?"0$mes":$mes)."-$ultimoDia";
            $dataVencimento = date('d/m/Y',strtotime($dataStr));

            //codigo do pagamento
            if($resultado->tipoativo=="Ações"){
                $codigo = "6015";
            }else {
                $codigo = "6015";
            }

            $darfGerada = [
                'nome'=>$resultado->user->name,
                'codigo'=>$codigo,
                'cpf'=>$resultado->user->cpf,
                'valor'=>$resultado->darf,
                'vencimento'=>$dataVencimento,
                'multa'=>0.0,
                'juros'=>0.0,
            ];
            $darfGerada['total'] = $darfGerada['valor'] + $darfGerada['multa'] + $darfGerada['juros'];
        }

        if($darfGerada==null){
            abort(403,"Não possivel processar essa DARF");
        }

        $data = compact('darfGerada');
        //dd($data);
        return view('relatorios.darf',$data);
    }
}
