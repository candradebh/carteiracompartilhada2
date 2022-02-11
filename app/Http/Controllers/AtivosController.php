<?php

namespace App\Http\Controllers;

use App\Models\Ativos;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class AtivosController extends Controller
{
    public function index(Request $request)
    {
        $ativos = Ativos::all();

        $data = compact('ativos');
        return view('ativos.index', $data);
    }

    public function create(Request $request)
    {

        return view('ativos.create');
    }

    public function show(Request $request)
    {
        $ticker = $request->get('ticker');
        $ativos = Ativos::orderBy('ticker', 'asc')->get();
        $ativo = Ativos::where('ticker', $ticker)->with('cotacoes')->first();

        if ($ativo == null) {
            abort(404, "Esse ativo não existe.");
        }
        //{'value': 1, 'volume': 1, 'open': 1, 'high': 2, 'low': 3, 'close': 4}

        $data = compact('ativo', 'ativos');

        //dd($ativo);
        return view('ativos.show', $data);
    }


    public function analise(Request $request)
    {
        $ticker = $request->get('ticker');
        $ativos = Ativos::orderBy('ticker', 'asc')->get();
        $ativo = Ativos::where('ticker', $ticker)->with('cotacoes')->first();

        if ($ativo == null) {
            abort(404, "Esse ativo não existe.");
        }
        //{'value': 1, 'volume': 1, 'open': 1, 'high': 2, 'low': 3, 'close': 4}
        $somaPreco = 0;
        $cotacoes = [];
        $i = 0;
        //dd($ativo);
        foreach ($ativo->cotacoes as $cotacao) {

            $somaPreco += $cotacao->close;
            //echo sizeof($cotacoes)."<br>";

            //media movel 14 dias
            if(sizeof($cotacoes) == 13){
                $ativo->mm14 = $somaPreco/(sizeof($cotacoes)+1);
                //dd($ativo->mm14);
            }

            //media movel de 30 dias
            if(sizeof($cotacoes) == 29){
                $ativo->mm30 = $somaPreco/(sizeof($cotacoes)+1);
            }

            //media movel de 180 dias
            if(sizeof($cotacoes) == 179){

                $ativo->mm180 = $somaPreco/(sizeof($cotacoes)+1);
            }

            //media movel de 1 ano
            if(sizeof($cotacoes) == 364){
                $ativo->mm365 = $somaPreco/(sizeof($cotacoes)+1);
            }

            //media movel de 2 anos
            if(sizeof($cotacoes) == 729){
                $ativo->mm730 = $somaPreco/(sizeof($cotacoes)+1);
            }

            $cotacoes[] = [date('Y-m-d', strtotime($cotacao->data)), $cotacao->open, $cotacao->hight, $cotacao->low, $cotacao->close, $cotacao->volume];

        }
        //dd($cotacoes);
        $ativo->dataAnalise = date('Y-m-d');
        $ativo->save();

        $tickerFile = "data/" . strtolower($ativo->ticker) . ".json";

        //grava o arquivo fomatado para api de grafico montar
        file_put_contents($tickerFile, json_encode($cotacoes));
        $data = compact('ativo', 'ativos', 'cotacoes', 'tickerFile');

        //dd($ativo);
        return view('ativos.analise', $data);
    }
    public function store(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = $request->user()->id;
        Ativos::create($data);

        return redirect()->route('ativos.index');
    }

    /**
     * Atualiza a cotação do ativo
     *
     * @param Request $request
     * @param [type] $ticker
     * @return void
     */
    public function cotacao(Request $request, $ticker)
    {

        $status = "";
        $ativo = Ativos::where('ticker', $ticker)->first();
        $api_key = Config::get('values.api_advantage_key');
        $ticker = preg_replace('/[^(\x20-\x7F)]*/', '', $ativo->ticker);
        $url = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=$ticker.SA&apikey=$api_key";
        echo $url;
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $url, ['verify' => false]);
        $cotacao = json_decode($response->getBody(), true);

        if (!isset($cotacao) || !isset($cotacao['Global Quote'])) {
            $status .= "Não $url <br>";
            $status.= "<br>" . (isset($cotacao['Error Message']) ? "Error: " . $cotacao['Error Message'] : "");
        }else{
            $status .= "Sim $url<br>";
        }

        if(isset($cotacao['Global Quote'])){
            $cotacao = $cotacao['Global Quote'];
            //dd($cotacao);
            $ativo->cotacao = $cotacao['05. price'];
            $ativo->dataCotacao = $cotacao['07. latest trading day']." 00:00:00";
            $ativo->save();
        }


        return redirect()->route('wallet.index');

    }
}
