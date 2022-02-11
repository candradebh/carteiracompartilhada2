<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\Ativos;
use App\Models\AtivosCarteiras;
use App\Models\Carteira;
use App\Models\Ordens;
use App\Models\Resultados;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

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
    public function index(Request $request)
    {
        //recebendo o ano no parametro da request
        $ano = $request->get('ano');

        if($ano==null){
            $ano = date('Y');
        }

        //anos que possui investimento
        $anos = Ordens::whereHas('carteira', function ($query) use ($request){
                            $query->where('carteiras.user_id',$request->user()->id);
                        })->get()
                        ->groupBy(function($val) {
                            return Carbon::parse($val->data)->format('Y');
                        });

        //se no ano atual eu ainda não fiz nenhum investimento eu adiciono o ano atual
        if(isset($anos[$ano])==false){
            $anos[$ano] = [];
        }

        $carteiras = Carteira::where('user_id',$request->user()->id)->with('ativos')->get();
        //dd(sizeof($carteiras));
        //usuario nao possui nenhuma carteira e deve criar
        if($carteiras == null ){
            redirect()->route('wallet.create');
        }
        //dd($carteiras);
        $ativosCarteira = AtivosCarteiras::with('carteiras','ativos')->get();

        //dd($ativosCarteira);
        /** so funciona com mysql
         * $ativosCarteira = DB::table('ativos_carteiras')
                                ->join('ativos', function ($join) {
                                    $join->on('ativos.id', '=', 'ativos_carteiras.ativo_id');
                                })
                                ->whereIn('carteira_id', $carteiras )
                                ->where('totalacumulado','<>', '0' )
                                ->orderBy('totalacumulado','desc')
                                ->get();
                                **/

        $ordens = Ordens::whereBetween('data', [date('Ymd',strtotime($ano."0101")), date('Ymd',strtotime($ano."1231"))])
                ->whereHas('carteira', function ($query) use ($request){
                    $query->where('carteiras.user_id',$request->user()->id);
                })
                ->orderBy('data')
                ->get()
                ->groupBy(function ($val) {
                    return Carbon::parse($val->data)->format('m');
                })->map(function ($row) {
                    return $row->sum('total');
                });
        $vendas = Ordens::where('tipoordem','V')->whereBetween('data', [date('Ymd',strtotime($ano."0101")), date('Ymd',strtotime($ano."1231"))])
                ->whereHas('carteira', function ($query) use ($request){
                    $query->where('carteiras.user_id',$request->user()->id);
                })
                ->orderBy('data')
                ->get()
                ->groupBy(function ($val) {
                    return Carbon::parse($val->data)->format('m');
                })->map(function ($row) {
                    return $row->sum('total');
                });
        $compras = Ordens::where('tipoordem','C')->whereBetween('data', [date('Ymd',strtotime($ano."0101")), date('Ymd',strtotime($ano."1231"))])
                ->whereHas('carteira', function ($query) use ($request){
                    $query->where('carteiras.user_id',$request->user()->id);
                })
                ->orderBy('data')
                ->get()
                ->groupBy(function ($val) {
                    return Carbon::parse($val->data)->format('m');
                })->map(function ($row) {
                    return $row->sum('total');
                });
        $acumulado = 0;
        $patrimonio = [];
        foreach($compras as $k=>$v ){
            $acumulado+=$v;
            $patrimonio[$k]=$acumulado;
        }
        $acumulado = 0;
        foreach($vendas as $k=>$v ){
            if(!isset($patrimonio[$k])){
                $patrimonio[$k]=$v*(-1);
            }else{
                $patrimonio[$k]=$patrimonio[$k]-$v;
            }
        }
        $ordensVendaPorMes = [];
        for($i=1;$i<=12;$i++){
            $ordensVendaPorMes[$i]=0;
        }
        foreach($vendas as $k=>$v){
            $ordensVendaPorMes[intval($k,10)]=$v;
        }

        $resultados = Resultados::where('user_id',$request->user()->id)
                            ->where('ano',$ano)
                            ->get()
                            ->groupBy('mes')
                            ->map(function ($row) {
                                return $row->sum('patrimonio');
                            });
        //garantir que o patrimonio seguira o ultimo valor atual
        $valorUltimoMes=0;
        foreach($resultados as $mes=>$res){

            if($mes == intVal(date('m'))){
                $valorUltimoMes = $res;

            }
            if($valorUltimoMes !== 0){
                $resultados[$mes] = $valorUltimoMes;
            }

        }

        //dd($resultados);

        $data = compact('ordens','vendas','compras','patrimonio','ativosCarteira','anos','ano','ordensVendaPorMes','resultados','carteiras');
        //dd($data);
        return view('home',$data);
    }


}
