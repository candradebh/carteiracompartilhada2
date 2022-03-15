<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ativos;
use App\Models\Carteira;
use App\Models\Corretoras;
use App\Models\Operacoes;
use App\Models\Ordens;
use App\Models\Resultados;
use App\Models\CarteiraAnualIrpf;
use App\Importadores\ImportarNotasBancoInterService;
use App\Importadores\ImportarNotasBancoXpService;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Smalot\PdfParser\Parser as PdfParser;

class ImportarCorretagensController extends Controller
{

    public function index(Request $request)
    {

        $corretoras = Corretoras::where('realizaimportacao', 1)->get(); //'Xp Investimentos'
        $carteiras = User::find($request->user()->id)->carteiras()->get();

        $data = compact('corretoras', 'carteiras');
        //return view('carteiras.importar', $data);

        return Inertia::render('Carteiras/Importar',[
            'corretoras' => Corretoras::where('realizaimportacao',true)->get(),
            'carteiras'=> Carteira::where('user_id', auth()->user()->id)->get()
        ]);
    }

    public function postUploadForm(Request $request)
    {
        ini_set('max_execution_time', 120);    
        
        // usando testes dados para importacao
        //$carteiraid = 1; //$request->get('carteira_id');
        //$corretora_id = 1;//$request->get('corretora_id');
        //$tipoImportacao = "todos";//$request->get('tipo');


        // deixe essa para prod
        $carteiraid = $request->get('carteira_id');
        $corretora_id = $request->get('corretora_id');
        $tipoImportacao = $request->get('tipo');
        if($tipoImportacao==null){
            $tipoImportacao = "todos";
        }
        $corretora = Corretoras::find($corretora_id);
        $nomeDiretorio = strtolower(str_replace(' ', '', $corretora->nome));

        $arquivos = [];
        $notas = [];
        $ordens = [];

        
        $importarOrdens = null;
        if ($corretora != null && $corretora->realizaimportacao == 1) {

            if ($nomeDiretorio == "inter") {
                $importarOrdens = new ImportarNotasBancoInterService (auth()->user(), $carteiraid, $corretora);
            } elseif ($nomeDiretorio == "xpinvestimentos") {
                $importarOrdens = new ImportarNotasBancoXpService(auth()->user(), $carteiraid, $corretora);
            }

        } else {
            abort(403, "Não importamos notas de corretagem dessa corretora");
        }
        
        //Reprocessar todos os arquivos de notas do usuario naquela corretora
        if (isset($tipoImportacao) && $tipoImportacao != null && $tipoImportacao == "todos") {

            //apagar as ordens importadas por arquivo para reimporta-las novamente
            DB::table('ordens')->where('carteira_id', $carteiraid)->where('corretora_id',$corretora_id)->where('origem', 'ARQUIVO')->delete();
            
            
            //lê todosos arquivos do diretorio da corretora
            $importarOrdens->getNotasCorretagem();
            
            // lê o conteudos dos arquivos
            $importarOrdens->lerNotas();
            

        } else {

            //arquivos importados
            $files = $request->file('upload');

            if ($request->hasFile('upload')) {

                foreach ($files as $file) {
                    //$nameFile = time() . '-' . $file->getClientOriginalName();
                    //$arquivos[] = $nameFile;
                    //$file->storeAs($directory, $nameFile);
                    //dd($file);
                    if ($file->getMimeType() == "application/pdf") {

                        $parser = new PdfParser();
                        //dd(file_get_contents($file));
                        //$pdf = $parser->parseContent(file_get_contents($file));
                        $pdf = $parser->parseFile($file);
                        $ordensImportadas = [];
                        //dd($pdf->getPages());
                        $nota = [];
                        //$text = $pdf->getText();
                        foreach ($pdf->getPages() as $p => $page) {
                            //dd($page->getText());
                            //dd($page->getTextArray());
                            //dd(nl2br($page->getText()));

                            $lines = explode("\n", $page->getText());
                            //dd($lines);
                            $ordensImportadas = $importarOrdens->getOrdens($lines, $ordensImportadas);
                            //dd($ordensImportadas);
                            $dataNota = $importarOrdens->getDataPregao($lines);

                            $explodeData = explode("/", $dataNota);

                            if ($dataNota == "") {
                                throw new Exception("Não foi possivel obter a data da nota de corretagem.");
                            }
                            $dia = $explodeData[0];
                            $mes = $explodeData[1];
                            $ano = $explodeData[2];
                            //dd($importarOrdens);
                            $directory = "/users/" . $request->user()->id . "/notas/$nomeDiretorio/$ano/$mes/";
                            $nomeArquivo = "$dia.pdf";
                            $path = $directory . $nomeArquivo;

                            $importarOrdens->files[] = $path;

                            $file->storeAs($directory, $nomeArquivo);

                            $nota = [
                                'data' => $dataNota,
                                'resumo' => $importarOrdens->getRodape($lines),
                                'path' => $path,
                            ];

                        }

                        $nota['ordens'] = $ordensImportadas;
                        $notas[] = $nota;
                    }
                }

                //adiciona as ordens importadas nos arquivos
                $importarOrdens->notas = $notas;

            }

        }

        // lê as ordens das notas lidas
        $importarOrdens->obterOrdensDasNotas();
        

        // grava isso no banco de dados se não tiver gravado
        $importarOrdens->gravarOrdensBanco();

        $this->processarPM(true, $request->user()->id);
        

        $this->calcularResultadosAcoes($request->user()->id);

        $this->calcularResultadosFundosImobiliarios($request->user()->id);

        dd($notas);
        return back()
            ->with('success', 'Você importou as notas')
            ->with('arquivos', $arquivos)
            ->with('ordens', $importarOrdens->ordens);
    }

    /**
     * calcular todas as carteira usando o preço médio de todos os usuarios
     *
     * Ex:
     *
     *
     * @return void
     */

    public function processarPM($imprimirLog, $userid)
    {
        $ano = 0;
        $anoAnterior = 0;
        $indice = 0;
        $ativosCarteiraDezembro = [];

        $stringImportacao = "";

        // Apaga os dados do usuário nas tabelas de resultados,carteiraanualirpf,ativos_carteiras, reseta quantidade na tabela de ordens
        $ordens1 = $this->limparDadosNoBanco($userid);

        foreach ($ordens1 as $ordem) {

            $ano = "" . date('Y', strtotime($ordem->data));

            //iniciando com o primeiro ano
            if ($anoAnterior == 0) {
                $anoAnterior = $ano;
            }

            //passou de 2019 para 2020 gravar os ativos do ultimo ano
            if ($anoAnterior != $ano ) {
                //echo "Anterior=$anoAnterior - Ano ATual=$ano<br>";

                $carteiraAtual = Carteira::where('id', $ordem->carteira_id)->with('ativos')->first();
                $ativosAtuais = $carteiraAtual->ativos()->wherePivot('quantidade', '!=', '0')->get();

                //adiciono usuario
                if (!isset($ativosCarteiraDezembro[$carteiraAtual->user_id])) {
                    $ativosCarteiraDezembro[$carteiraAtual->user_id] = [];
                }
                //adiciono ano e ativos
                if (!isset($ativosCarteiraDezembro[$carteiraAtual->user_id][$anoAnterior])) {
                    $ativosCarteiraDezembro[$carteiraAtual->user_id][$anoAnterior] = $ativosAtuais;
                }

                //imprime uma tabela com as posiçoes
                //$this->imprimePosicoes($ano, $ativosCarteiraDezembro, "Fundos Imobiliários");

                //dd($ativosAtuais);
                $anoAnterior = $ano;

            }

            $indice++;

            $despesas = $ordem->despesas + $ordem->outras_despesas;

            $carteira = Carteira::where('id', $ordem->carteira_id)->with('ativos')->first();
            $ativo = $carteira->ativos()->wherePivot('ativo_id', $ordem->ativo_id)->first();

            $stringOperacao = "";
            $stringOperacao .= "<br>-------------------------------------------------------------------------------------------</br>";

            //se o ativo não existir na carteira eu adiciono
            if ($ativo == null) {

                $carteira->ativos()->attach($ordem->ativo_id);
                $ativo = $carteira->ativos()->wherePivot('ativo_id', $ordem->ativo_id)->first();

            }

            //log da ordem
            $stringOperacao .= "<p style='color:green'>ATIVO id: $ordem->ativo_id ";
            $stringOperacao .= "Ticker: $ativo->ticker</p>";
            $stringOperacao.="<br>ANTES DO PROCESSAR";
            $color = $ordem->tipoordem == "C" ? 'blue' : 'red';
            $stringOperacao .= "<p style='color: $color'>ID ordem:" . $ordem->id . " Tipo da Ordem: " . $ordem->tipoordem . "<br>Data: $ordem->data";
            $stringOperacao .= "<br>Carteira do usuario: " . $ordem->carteira_id . "</br>";
            $stringOperacao .= "<br>Origem registro:  $ordem->origem | Corretora $ordem->corretora_id </br>";
            $stringOperacao .= "<br>Ativo ($ordem->ativo_id): $ativo->ticker | Tipo: ".$ordem->ativo->categoria;
            $stringOperacao .= " | Quantidade: $ordem->quantidade  | Preco Unidade: $ordem->preco | Despesas: $despesas";
            $stringOperacao .= " |Total ordem: $ordem->total | saldo: $ordem->saldo Split_id: $ordem->split_id</p>";
            $stringOperacao .= "<br><br>Dados do ativo na carteira:<br>Quantidade Carteira: " . $ativo->pivot->quantidade;
            $stringOperacao .= "<br>Total Carteira: " . $ativo->pivot->total;
            $stringOperacao .= "<br>Preco Médio: " . $ativo->pivot->precomedio;
            $stringOperacao .= "<br>Acumulado de operacoes: " . $ativo->pivot->totalacumulado;

            //para um tipo de acao no ano criar todos os resultados
            $resultado = $this->obterOuCriarTabelaResultados($ordem, $ano);

            //somando as despesas
            $resultado->despesas += $despesas;
            $ativo->save();

            //antes de qualquer calculo, temos que calcular operacoes de split, inplit e fusao //$stringOperacao .=
            $stringOperacao .= $this->processarOperacoesFusao($ordem);

            //pegando o objeto atualizado apos a fusao
            $ativo = $carteira->ativos()->wherePivot('ativo_id', $ordem->ativo_id)->first();

            if (strtoupper($ordem->tipoordem) == "C") {

                $stringOperacao .= "<br><br>COMPRA !!!! -- Indice $indice <br>";

                $resultado->compras += $ordem->total;
                $resultado->patrimonio += $ordem->total;

                //somar a quandidade do ativo na carteira
                $stringOperacao .= "Quantidade na carteira: " . $ativo->pivot->quantidade . " + quantidade da ordem " . $ordem->quantidade . " = ";
                $ativo->pivot->quantidade += $ordem->quantidade;
                $stringOperacao .= $ativo->pivot->quantidade . "<br>";

                //Somar o total na carteira + o total na ordem + as despesas da ordem
                $stringOperacao .= "Total na carteira: " . $ativo->pivot->total . " + total da ordem " . $ordem->total . " + total de despesas (" . $ordem->despesas . " + " . $ordem->outras_despesas . ")  = ";
                $ativo->pivot->total += $ordem->total + ($ordem->despesas + $ordem->outras_despesas);
                $stringOperacao .= $ativo->pivot->total . "<br>";

                //quando se esta vendido e 0 a quantidade
                if ($ativo->pivot->quantidade == 0) {
                    $stringOperacao .= "PREÇO MÉDIO  é igual a 0 <br>";
                    $ativo->pivot->precomedio = 0;
                    $ativo->pivot->total = 0;
                    //tenho que zerar a ordem de venda com saldo

                } else if ($ativo->pivot->quantidade > 0) {
                    $stringOperacao .= "PREÇO MÉDIO  na carteira é o total " . $ativo->pivot->total . " dividido pelo QUANTIDADE NA CARTEIRA " . $ativo->pivot->quantidade . " = ";
                    $ativo->pivot->precomedio = $ativo->pivot->total / $ativo->pivot->quantidade;
                    $stringOperacao .= $ativo->pivot->precomedio . " que Arredondado fica (" . number_format($ativo->pivot->precomedio, 2, ",", ".") . " )<br>";
                } else {
                    $stringOperacao .= "PREÇO MÉDEO ja calculei na venda short pois a quantidade é menor que 0<br>";
                }

                $stringOperacao .= $this->zerarPosicaoOrdens($ordem);

            }

            if (strtoupper($ordem->tipoordem) == "V") {

                $stringOperacao .= "<br><br>VENDA !!!! -- Indice $indice <br>";


                $resultado->vendas += $ordem->total;
                $resultado->patrimonio = $resultado->patrimonio - $ordem->total;

                //Antes de vender, deve verificar se essa ordem possui operações DE PREÇO nessa data e aplicar antes de vender
                // exemplo: comprou uma acao que realizou split ou inplit e na hora de vender calculou errado os valores
                //$stringOperacao .=
                $this->processarOperacoes($ordem);

                //somar a quandidade do ativo na carteira
                $stringOperacao .= "Quantidade na carteira: " . $ativo->pivot->quantidade . " + quantidade da ordem " . ($ordem->quantidade * -1) . " = ";
                $ativo->pivot->quantidade += ($ordem->quantidade * -1);
                $stringOperacao .= $ativo->pivot->quantidade . "<br>";

                //Somar o total da ordem menos a despesa
                $stringOperacao .= " Total ordem de venda é quantidade $ordem->quantidade x $ordem->preco - ( $ordem->despesas x $ordem->outras_despesas) = ";
                $totalOrdemVenda = $ordem->quantidade * $ordem->preco - ($ordem->despesas + $ordem->outras_despesas);
                $stringOperacao .= $totalOrdemVenda . "<br>";

                //preco medio da venda
                $stringOperacao .= "Preco medio da venda é  $totalOrdemVenda / $ordem->quantidade  = ";
                $precoMedioVendaPorAcao = $totalOrdemVenda / $ordem->quantidade;
                $stringOperacao .= $precoMedioVendaPorAcao . "<br>";

                //lucro apurado da venda
                $stringOperacao .= "O lucro apurado é ($ordem->quantidade x $precoMedioVendaPorAcao) - ($ordem->quantidade x " . $ativo->pivot->precomedio . " )  = ";
                $lucroApurado = ($ordem->quantidade * $precoMedioVendaPorAcao) - ($ordem->quantidade * $ativo->pivot->precomedio);
                $stringOperacao .= $lucroApurado . "<br>";

                //acumulado de lucros e prejuizos da acao na carteira
                $ativo->pivot->totalacumulado += $lucroApurado;

                //resultado da operacao na tabela de resultados para calculo da darf
                $stringOperacao .= "Resultado mensal $resultado->mes/$resultado->ano atual é $resultado->resultado  + $lucroApurado = ";
                $resultado->resultado += $lucroApurado;
                $stringOperacao .= "$resultado->resultado <br>";

                //total na carteira
                $stringOperacao .= "Total na carteira igual a quantidade que sobrou " . $ativo->pivot->quantidade;
                if ($ativo->pivot->quantidade > 0) {

                    $ativo->pivot->total = $ativo->pivot->quantidade * $ativo->pivot->precomedio;
                    $stringOperacao .= " * o preco medio que ja esta la  " . $ativo->pivot->precomedio . "  = ";

                } else if ($ativo->pivot->quantidade < 0) {

                    $ativo->pivot->total = $ativo->pivot->quantidade * $precoMedioVendaPorAcao;
                    $ativo->pivot->precomedio = $precoMedioVendaPorAcao;
                    $stringOperacao .= " * o preco medio da venda  $precoMedioVendaPorAcao  = ";

                } else {

                    $ativo->pivot->total = 0;
                    $stringOperacao .= " e o preço médio é 0, sendo o total na carteira =  <br>";

                }

                $stringOperacao .= $ativo->pivot->total . "<br>";

                //ajusto nas ordens para quando clicar na carteira e ver as ordens que compoem aquela quandtidade
                $stringOperacao .= $this->zerarPosicaoOrdens($ordem);

                $stringOperacao .= "Saldo dessa ordem agora é  $ordem->saldo <br>";

            } //fim ordem de venda

            //salvar o resultado da compra ou da venda

            $resultado->save();
            $ativo->pivot->save();
            $ativo->save();


            $stringImportacao .= $stringOperacao;
            //dd($ativo);

        } //foreach ordens

        //ativos na carteira no ultimo dia do ano para irpf, salvar isso numa tabela
        //dd($ativosCarteiraDezembro);
        if (sizeof($ativosCarteiraDezembro) > 0) {
            foreach ($ativosCarteiraDezembro as $user => $anos) {
                //dd($anos);
                if (sizeof($anos) > 0) {
                    foreach ($anos as $ano => $ativos) {
                        foreach ($ativos as $ac) {
                            CarteiraAnualIrpf::create(['ano' => $ano, 'user_id' => $user, 'ativo_id' => $ac->id,
                                'quantidade' => $ac->pivot->quantidade, 'precomedio' => $ac->pivot->precomedio,
                                'total' => $ac->pivot->total]);
                        }
                    }
                }
            }
        }

        echo $imprimirLog ? $stringImportacao : "Sem Log em processar PM";

    }

    /**
     * Açoes: Vendas acima de 20mil paga darf e o valor da darf pode ser deduzido pelo prejuizo acumulado
     *
     * FIIs: Todos os lucros são taxados em 15% e prejuizos podem ser deduzidos de acumulados
     *
     */
    public function calcularResultadosAcoes($userid)
    {
        $mesAtual = date('m');
        $anoAtual = date('Y');

        if ($userid != null) {
            //echo "diferente null";
            $usuarios = User::where('id', $userid)->get();
        } else {
            $usuarios = User::get();
        }

        foreach ($usuarios as $usuario) {

            $resultados = Resultados::where('tipoativo', 'Ações')->where('user_id', $usuario->id)
                ->orderBy('user_id', 'asc')
                ->orderBy('ano', 'asc')
                ->orderBy('mes', 'asc')
                ->get();

            $prejuizoacumulado = 0.0;
            $patrimonioAcumulado = 0.0;

            //patrimonio e prejuizo devem ser levados para o proximo
            $dados = [];

            foreach ($resultados as $resultado) {
                //nao vamos calcular futuro
                if ($resultado->mes > $mesAtual && $resultado->ano == $anoAtual) {
                    continue;
                }

                $patrimonioAcumulado += $resultado->patrimonio;

                //verifico se teve dados do ano anterior para trazer
                if (isset($dados[$resultado->ano][$resultado->mes])) {

                    $resultado->prejuizoacumulado = $dados[$resultado->ano][$resultado->mes]["prejuizoacumulado"];
                    $resultado->patrimonio = $dados[$resultado->ano][$resultado->mes]["patrimonio"];

                }

                $darf = 0.0;

                $string = "User: $resultado->user_id Tipo $resultado->tipoativo - " . $resultado->ano . "-" . $resultado->mes . "| Resultado = " . $resultado->resultado . "| PrejuizoAcumulado = " . $prejuizoacumulado . " ( Vendas " . $resultado->vendas . " ) ";

                //ultrapassou o limit Açaoes
                if (intVal($resultado->vendas) > 20000.01 && intVal($resultado->resultado) > 0) {

                    $impostoDevido = ($resultado->resultado + $prejuizoacumulado) * 0.15;
                    $string .= "Imposto devido = ($resultado->resultado + $prejuizoacumulado) * 0.15  =  $impostoDevido <br>";
                    $darf = $impostoDevido;

                    if (intVal($darf) > 10) {

                        $resultado->darf = $darf;

                        //caso tenha pago darf nesse mes vamos zerar o proximo mes prejuizo acumulado e levar o patrimonio
                        if ($resultado->mes == 12) {
                            //dd($resultado);
                            $dados[($resultado->ano + 1)] = [1 => ["prejuizoacumulado" => 0.0, "patrimonio" => $patrimonioAcumulado]];
                        } else {
                            $dados[$resultado->ano] = [($resultado->mes + 1) => ["prejuizoacumulado" => 0.0, "patrimonio" => $patrimonioAcumulado]];
                        }

                    } else if (intVal($darf) < 0) {
                        $prejuizoacumulado = $resultado->resultado + ($prejuizoacumulado);
                    }
                }

                //se a darf nao foi calculada pode acumular prejus
                if (intVal($resultado->resultado) < 0 && intVal($resultado->darf) == 0) {

                    if (isset($dados[$resultado->ano][$resultado->mes])) {
                        $prejuizoacumulado = $resultado->resultado;
                    } else {
                        $prejuizoacumulado += $resultado->resultado;
                    }

                }

                $resultado->prejuizoacumulado = $prejuizoacumulado;
                $resultado->patrimonio = $patrimonioAcumulado;
                $resultado->save();

                //caso o ultimo mes do ano levar o acumulado para janeiro
                if ($resultado->mes == 12) {

                    $dados[($resultado->ano + 1)] = [1 => ["prejuizoacumulado" => $prejuizoacumulado, "patrimonio" => $patrimonioAcumulado]];
                    if ($resultado->ano == 2021) {
                        //dd($dados);
                    }
                }

                echo (intVal($resultado->vendas) > 20000 ? "<p style='color:red'>" : "<p>") . " $string -> Darf = $darf </p>";

            }
        }
    }

    /**
     * Açoes: Vendas acima de 20mil paga darf e o valor da darf pode ser deduzido pelo prejuizo acumulado
     *
     * FIIs: Todos os lucros são taxados em 15% e prejuizos podem ser deduzidos de acumulados
     *
     */
    public function calcularResultadosFundosImobiliarios($userid)
    {
        $mesAtual = date('m');
        $anoAtual = date('Y');

        if ($userid != null) {
            //echo "diferente null";
            $usuarios = User::where('id', $userid)->get();
        } else {
            $usuarios = User::get();
        }

        foreach ($usuarios as $usuario) {

            $resultados = Resultados::where('tipoativo', 'Fundos Imobiliários')->where('user_id', $usuario->id)
                ->orderBy('user_id', 'asc')
                ->orderBy('ano', 'asc')
                ->orderBy('mes', 'asc')
                ->get();

            $prejuizoacumulado = 0.0;
            $patrimonioAcumulado = 0.0;

            //patrimonio e prejuizo devem ser levados para o proximo
            $dados = [];

            foreach ($resultados as $resultado) {

                //nao vamos calcular futuro
                if ($resultado->mes > $mesAtual && $resultado->ano == $anoAtual) {
                    continue;
                }

                $patrimonioAcumulado += $resultado->patrimonio;

                //verifico se teve dados do ano anterior para trazer
                if (isset($dados[$resultado->ano][$resultado->mes])) {

                    $resultado->prejuizoacumulado = $dados[$resultado->ano][$resultado->mes]["prejuizoacumulado"];
                    $resultado->patrimonio = $dados[$resultado->ano][$resultado->mes]["patrimonio"];

                }

                $darf = 0.0;

                $string = "User: $resultado->user_id Tipo $resultado->tipoativo - " . $resultado->ano . "-" . $resultado->mes . "| Resultado = " . $resultado->resultado . "| PrejuizoAcumulado = " . $prejuizoacumulado . " ( Vendas " . $resultado->vendas . " ) ";

                //teve lucro
                if (intVal($resultado->resultado) > 0) {

                    $impostoDevido = ($resultado->resultado + $prejuizoacumulado) * 0.20;
                    $string .= "Imposto devido = ($resultado->resultado + $prejuizoacumulado) * 0.20  =  $impostoDevido <br>";
                    $darf = $impostoDevido;

                    if (intVal($darf) > 10) {

                        $resultado->darf = $darf;

                        //caso tenha pago darf nesse mes vamos zerar o proximo prejuizo acumulado
                        if ($resultado->mes == 12) {
                            $dados[($resultado->ano + 1)] = [1 => ["prejuizoacumulado" => 0.0, "patrimonio" => $patrimonioAcumulado]];
                        } else {
                            $dados[$resultado->ano] = [($resultado->mes + 1) => ["prejuizoacumulado" => 0.0, "patrimonio" => $patrimonioAcumulado]];
                        }

                    } else if (intVal($darf) < 0) {
                        $prejuizoacumulado = $resultado->resultado + ($prejuizoacumulado);
                    }
                }

                //se a darf nao foi calculada pode acumular prejus
                if (intVal($resultado->resultado) < 0 && intVal($resultado->darf) == 0) {

                    if (isset($dados[$resultado->ano][$resultado->mes])) {
                        $prejuizoacumulado = $resultado->resultado;
                    } else {
                        $prejuizoacumulado += $resultado->resultado;
                    }

                }

                $resultado->prejuizoacumulado = $prejuizoacumulado;
                $resultado->patrimonio = $patrimonioAcumulado;
                $resultado->save();

                if ($resultado->mes == 12) {

                    $dados[($resultado->ano + 1)] = [1 => ["prejuizoacumulado" => $prejuizoacumulado, "patrimonio" => $patrimonioAcumulado]];
                    if ($resultado->ano == 2020) {
                        //dd($dados);
                    }
                    //dd($dados);
                }

                echo (intVal($resultado->resultado) < 0 ? "<p style='color:red'>" : "<p>") . " $string -> Darf = $darf </p>";

            }
        }
        //dd($dados);
    }

    public function zerarPosicaoOrdens($ordem)
    {
        $stringZerarSaldos = " Zerando posiçoes<br>";

        $ordensParaZerar = Ordens::where('ativo_id', $ordem->ativo_id)
            ->where('tipoordem', (strtoupper($ordem->tipoordem) == "C" ? "V" : "C"))
            ->where('carteira_id', $ordem->carteira_id)
            ->where('saldo', '<>', 0)
            ->where('data', '<=', $ordem->data)
            ->orderBy('quantidade', 'desc')
            ->get();

        $quantidadeParaZerar = $ordem->quantidade;
        foreach ($ordensParaZerar as $ordemParaZerar) {

            if ($ordemParaZerar->saldo == 0) {
                continue;
            }

            $stringZerarSaldos .= "A ordem id $ordemParaZerar->id,  do tipo $ordemParaZerar->tipoordem, tinha saldo  $ordemParaZerar->saldo e agora tem ";
            $possoSubtrairOrdem = $quantidadeParaZerar - $ordemParaZerar->saldo;

            //saldo igual a quantidade vendida
            if ($possoSubtrairOrdem == 0) {

                $ordemParaZerar->saldo = 0;
                $ordem->saldo = 0;
                $quantidadeParaZerar = 0;

                //a venda ja foi toda zerada, o saldo da ordem é maior que a venda
            } elseif ($possoSubtrairOrdem < 0) {

                $ordemParaZerar->saldo -= $quantidadeParaZerar;
                $quantidadeParaZerar = 0;

                //zerou a quantidade da ordem e precisa zerar outras
            } else {
                $ordemParaZerar->saldo = 0;
                $quantidadeParaZerar = $possoSubtrairOrdem;
            }

            $ordemParaZerar->save();
            $stringZerarSaldos .= $ordemParaZerar->saldo . "<br>";

            $stringZerarSaldos .= "<br>A ordem $ordem->id atual tem $ordem->saldo e agora tem  ";
            $ordem->saldo -= $possoSubtrairOrdem;
            $ordem->save();
            $stringZerarSaldos .= $ordem->saldo . "<br>";

            //ja zerou o que pode e pode parar
            if ($quantidadeParaZerar == 0) {
                $ordem->saldo = 0;
                $ordem->save();
                break;
            }

        }
        //dd($ordensParaZerar);

        return $stringZerarSaldos;

    }

    /**
     *
     * Testar operacoes entre uma e outra do mesmo ativo
     *
     * ex magulu fez dois splits
     *
     * @param [type] $ordem
     * @return void
     */
    public function processarOperacoes($ordem)
    {

        //operacao de fusao 19/07 fusao btow3 com lame3 virou amer3
        $operacoes = Operacoes::where('data', '<=', date('Y-m-d', strtotime($ordem->data)))->where('ativo_id', $ordem->ativo_id)->get();

        $strOperacoes = "";
        $strOperacoes .= "<br>--------------------- OPERACOES : " . sizeof($operacoes);

        foreach ($operacoes as $operacao) {

            $strOperacoes .= "<br>Data:" . $operacao->data;
            $strOperacoes .= "<br>TIPO: $operacao->tipooperacao  ";
            $strOperacoes .= "<br>valor original: $operacao->valor_original<br> valor Alterado: $operacao->valor_alterado <br> Novo ticker: $operacao->novoticker";

            //ordens para alterar
            $ordensParaAlterar = Ordens::where('ativo_id', $operacao->ativo_id)
                ->where('carteira_id', $ordem->carteira_id)
                ->where('tipoordem', 'C')
                ->where('saldo', '>', 0)
                ->where('data', '<=', $operacao->data)
                ->whereNull('split_id')
                ->orderBy('data', 'asc')
                ->get();

            foreach ($ordensParaAlterar as $oa) {

                $array = explode(':', $operacao->proporcao);
                $strOperacoes .= "<br> ANTES ";
                $strOperacoes .= "<br>Ordem data " . $oa->data . ", quantidade " . $oa->quantidade . ",preco " . $oa->preco . " ativo $oa->ativo_id";

                if ($operacao->tipooperacao == 'split' && $oa->split_id == null) {
                    $strOperacoes .= "<br>Proporcao: $operacao->proporcao";
                    $divide = $array[0];
                    $multiplica = $array[1];
                    $novaQuantidade = ($oa->quantidade / $divide) * $multiplica;
                    $dataUpdate = ['quantidade' => $novaQuantidade, 'preco' => $operacao->valor_alterado, 'split_id' => $operacao->id,
                        'split_quantidade_origem' => $oa->quantidade, 'split_valor_origem' => $oa->preco, 'split_data' => $operacao->data,
                        'saldo' => $novaQuantidade,
                    ];
                    $oa->update($dataUpdate);

                }

                $oa->save();

                $strOperacoes .= "<BR>DEPOIS<br> ticker " . $oa->ativo->ticker;
                $strOperacoes .= "<br>Ordem data" . $oa->data . ", quantidade " . $oa->quantidade . ",preco
                                    " . $oa->preco . " quantidade origem " . $oa->split_quantidade_origem . " valor origem " . $oa->split_valor_origem . " ativo $oa->ativo_id";
                $strOperacoes .= "----------------------<br>";
                //echo $strOperacoes;

            } //for

        }

        return $strOperacoes;
    }

    /**
     *
     * FUSAO AMER3 e BTOW3
     *
     * ex magulu fez dois splits
     *
     * @param [type] $ordem
     * @return void
     */
    public function processarOperacoesFusao($ordem)
    {
        $strOperacoes = "";

        //pode ser o ativo que gerou a operacao ou o novo ticker
        $operacoes = Operacoes::where('tipooperacao', 'fusao')
            ->where('data', '<=', date('Y-m-d', strtotime($ordem->data)))
            //->where('ativo_id', $ordem->ativo_id)
            ->where('novoticker', $ordem->ativo->ticker)->get();

        if (sizeof($operacoes) > 0 && $ordem->split_id == null) {

            $strOperacoes .= "<br>--------------------- OPERACAO DE FUSAO : " . sizeof($operacoes);

            foreach ($operacoes as $operacao) {

                $strOperacoes .= "<br>Data Operacao:" . $operacao->data;
                $strOperacoes .= "<br>TIPO OPERACAO: $operacao->tipooperacao  ";
                $strOperacoes .= "<br>ticker antigo ". $operacao->ativo->ticker." Novo ticker: $operacao->novoticker";

                $novo = Ativos::where('ticker', $operacao->novoticker)->first();

                //ordens para alterar
                $ordensParaAlterar = Ordens::where('ativo_id', $operacao->ativo_id)
                    ->where('ativo_id', '<>', $novo->ativo_id)
                    ->where('carteira_id', $ordem->carteira_id)
                    ->where('tipoordem', 'C')
                    ->where('saldo', '>', 0)
                    ->where('data', '<=', $operacao->data)
                    ->whereNull('split_id')
                    ->orderBy('data', 'asc')
                    ->get();

                $carteira  = Carteira::find($ordem->carteira_id)->first();

                $strOperacoes.="<br> Ordens para alterar ".sizeof($ordensParaAlterar);

                foreach ($ordensParaAlterar as $oa) {

                    $strOperacoes .= "<br> ORDEM ANTES: data " . $oa->data . ", quantidade " . $oa->quantidade;
                    $strOperacoes . ",preco " . $oa->preco . " ativo $oa->ativo_id Split_id $oa->split_id";

                    if ($operacao->tipooperacao == 'fusao' && $oa->split_id == null && $oa->ativo_id !== $novo->id) {

                        $strOperacoes .= "<br> ";

                        //tenho que tirar essa ordem da carteira BTOW3
                        $ativoAntigo = $carteira->ativos()->wherePivot('ativo_id', $oa->ativo_id)->first();
                        $strOperacoes .= "<br> Antigo quantidade ".$ativoAntigo->pivot->quantidade;
                        $ativoAntigo->pivot->quantidade-=$oa->quantidade;
                        $ativoAntigo->pivot->save();
                        $strOperacoes .= "<br> After Save Antigo quantidade ".$ativoAntigo->pivot->quantidade;

                        //passo a ordem para o novo ativo BTOW3->AMER3
                        $oa->ativo_id = $novo->id;
                        $oa->split_id = $operacao->id;


                        $ativoNovo = $carteira->ativos()->wherePivot('ativo_id', $novo->id)->first();
                        $strOperacoes .= "<br> Novo quantidade ".$ativoNovo->pivot->quantidade;
                        $ativoNovo->pivot->quantidade+=$oa->quantidade;
                        $ativoNovo->pivot->save();
                        $strOperacoes .= "<br> After Save Novo quantidade ".$ativoNovo->pivot->quantidade;
                    }

                    $oa->save();

                    $strOperacoes .= "<BR>ORDEM DEPOIS: data" . $oa->data . ", quantidade " . $oa->quantidade . ",preco " . $oa->preco . " ativo $oa->ativo_id Split_id $oa->split_id";
                    $strOperacoes .= "----------------------<br>";

                } //for

            }
        }

        return $strOperacoes;
    }

    public function definirCategoria($ticker)
    {

        $categoria = 'Ações';

        if (strlen($ticker) == 6) {
            $categoria = 'Fundos Imobiliário';
        }
        return $categoria;
    }

    public function obterOuCriarTabelaResultados($ordem, $ano)
    {

        $todosResultadosPorTipo = Resultados::where('user_id', $ordem->carteira->user_id)
            ->where('ano', $ano)
            ->where('tipoativo', $ordem->ativo->categoria)->get();

        if (sizeof($todosResultadosPorTipo) == 0) {
            for ($i = 1; $i <= 12; $i++) {
                Resultados::create([
                    'user_id' => $ordem->carteira->user_id,
                    'tipoativo' => $ordem->ativo->categoria,
                    'ano' => $ano,
                    'mes' => $i,
                ]);
            }
        }
        //pega o resultado do mes da ordem
        $resultado = Resultados::where('user_id', $ordem->carteira->user_id)
            ->where('ano', date('Y', strtotime($ordem->data)))
            ->where('mes', date('m', strtotime($ordem->data)))
            ->where('tipoativo', $ordem->ativo->categoria)->first();

        return $resultado;
    }

    public function imprimePosicoes($ano, $dados, $categoria)
    {
        foreach ($dados as $user => $anos) {
            echo "Usuario: $user<br>";
            foreach ($anos as $ano => $ativos) {
                echo "Ano: $ano<br>";
                echo "<table border=1>";
                foreach ($ativos as $at) {

                    if ($categoria == null) {
                        $qtd = $at->pivot->quantidade;
                        $tot = $at->pivot->total;
                        echo "<tr><td> $at->ticker </td><td>$qtd</td><td>$tot</td></tr>";
                    } else if ($at->categoria == $categoria) {
                        $qtd = $at->pivot->quantidade;
                        $tot = $at->pivot->total;
                        echo "<tr><td> $at->ticker </td><td>$qtd</td><td>$tot</td></tr>";
                    }
                }
                echo "</table>";
            }

        }

    }

    /**
     *
     * Apaga os dados do usuário nas tabelas de resultados,carteiraanualirpf,ativos_carteiras, reseta quantidade na tabela de ordens
     *
     * @param [type] $userid
     * @return []
     */
    public function limparDadosNoBanco($userid){

        $ordens1=null;

        if ($userid == null) {
            //esse if é no inicio da aplicação
            DB::table('resultados')->truncate();
            DB::table('carteiraanualirpf')->truncate();
            DB::table('ativos_carteiras')->truncate();
            DB::update('UPDATE ordens SET saldo = quantidade');

            $ordens1 = Ordens::with('carteira')->orderBy('data')->get();

        } else {

            DB::table('resultados')->where('user_id', $userid)->delete();
            DB::table('carteiraanualirpf')->where('user_id', $userid)->delete();
            $carteiraIds = [];

            //deletar os ativos da minha carteira
            $carteiras = Carteira::with(['ativos','ordens'])->where('user_id', $userid)->get();
            foreach($carteiras as $carteira){
                $carteiraIds [] = $carteira->id;
            }

            DB::table('ativos_carteiras')->whereIn('carteira_id', $carteiraIds)->delete();

            //resetar quantidades para recalcular
            $ordens = Ordens::whereIn('carteira_id', $carteiraIds)->get();
            foreach ($ordens as $ordem) {
                $ordem->saldo = $ordem->quantidade;
                $ordem->save();
            }


            $ordens1 = Ordens::with('carteira', 'ativo')->whereIn('carteira_id', $carteiraIds)->orderBy('data')->get();

        }

        return $ordens1;

    }

}
