<?php

namespace App\Http\Controllers;

use App\Models\CarteiraAnualIrpf;
use Illuminate\Http\Request;
use App\User;
use App\Models\Ordens;
use App\Models\Resultados;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Date;

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

        $carteiras = User::find($request->user()->id)->carteiras()->get();

        $anos = Ordens::whereHas('carteira', function ($query) use ($request){
                            $query->where('carteiras.user_id',$request->user()->id);
                        })->get()
                        ->groupBy(function($val) {
                            return Carbon::parse($val->data)->format('Y');
                        });


        $ano = $request->get('ano');
        if($ano==null){
            $ano = date('Y');
        }
        $posicaoAnual = CarteiraAnualIrpf::where('user_id',$request->user()->id)->where('ano',$ano)->get();
        $resultadosAcoes = Resultados::where('tipoativo','Ações')->where('ano',$ano)->where('user_id',$request->user()->id)->orderBy('mes','asc')->get();
        $resultadosFii = Resultados::where('tipoativo','Fundos Imobiliários')->where('ano',$ano)->where('user_id',$request->user()->id)->orderBy('mes','asc')->get();
        $data = compact('ano','anos','resultadosAcoes','resultadosFii','carteiras','posicaoAnual');
        //dd($data);
        return view('relatorios.index',$data);
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
