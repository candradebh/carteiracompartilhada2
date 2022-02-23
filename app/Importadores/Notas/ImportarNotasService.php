<?php

namespace App\Importadores\Notas;


use App\Models\Ativos;
use App\Models\Corretoras;
use App\Models\Ordens;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;

abstract class ImportarNotasService
{

    protected $user;
    protected $directory;
    public $corretora;
    public $carteira;
    public $files;
    public $notas;
    public $ordens;

    public function __construct(User $user, $carteira, Corretoras $corretora)
    {
        $this->corretora = $corretora;
        $this->user = $user;
        $this->carteira = $carteira;
        $this->directory = "/users/" . $this->user->id . "/notas";
        $this->files = [];
        $this->notas = [];
        $this->ordens = [];

    }

    abstract function lerNotas();



    /**
     *
     * Preenche o array de ordens com as notas importadas dos arquivos
     * Ativos que não existem ele cadastra
     *
     * @return void
     */
    public function obterOrdensDasNotas()
    {

        foreach ($this->notas as $nota) {

            //dd($nota);
            $strData = $nota['data'];
            $strDataUs = implode('-', array_reverse(explode('/', $strData)));
            $dataOrdemBanco = date('Y-m-d H:i:s', strtotime($strDataUs . ' 12:00:00'));

            $i = 1;
            foreach ($nota['ordens'] as $ordem) {

                $ativo = Ativos::where('ticker', $ordem['ticker'])->first();
                $data = date('Y-m-d H:i:s', strtotime($dataOrdemBanco . "+$i minutes"));
                $i++;

                //crio o ativo se nao existe
                if ($ativo == null) {
                    $categoria = $this->definirCategoria($ordem['ticker']);
                    $ativo = Ativos::create(['ticker' => $ordem['ticker'], 'nome' => $ordem['nome'], 'setor' => '-', 'classe' => '-', 'categoria' => $categoria, 'cotacao' => 0.0, 'dataCotacao' => null]);
                }

                $precoordem = str_replace(',', '.', str_replace('.', '', $ordem['preco']));
                $totalordem = str_replace(',', '.', str_replace('.', '', $ordem['total']));
                $quantidadeordem = str_replace(',', '.', str_replace('.', '', $ordem['qtd']));

                $this->ordens[] = [
                    'tipoordem' => $ordem['tipoordem'], 'carteira_id' => $this->carteira, 'corretora_id' => $ordem['corretora_id'],
                    'ativo_id' => $ativo->id, 'quantidade' => $quantidadeordem, 'preco' => $precoordem, 'despesas' => 0,
                    'total' => $totalordem, 'data' => $data, 'saldo' => $quantidadeordem, 'origem' => 'ARQUIVO', 'path' => $nota['path'],
                ];

            }

        }

        //existem ordens para importar
        if (sizeof($this->ordens) > 0) {

            foreach ($this->ordens as $ordem) {

                $ordemBanco = Ordens::where('ativo_id', $ordem['ativo_id'])
                    ->where('corretora_id', $ordem['corretora_id'])
                    ->where('quantidade', $ordem['quantidade'])
                    ->where('carteira_id', $ordem['carteira_id'])
                    ->where('tipoordem', $ordem['tipoordem'])
                    ->where('data', $ordem['data'])
                    ->get();

                if (sizeof($ordemBanco) == 0) {
                    DB::table('ordens')->insert($ordem);
                    //echo "Insert Data".$ordem['data']." - Tipo".$ordem['tipoordem']." - ".$ordem['carteira_id']." - ".$ordem['ativo_id']." <br>";
                }
            }

        }

    }
    /**
     *
     * Percorre o array de ordens inserindo-os no banco de dados
     *
     * @return void
     */
    public function gravarOrdensBanco(){

        if (sizeof($this->ordens) > 0) {

            foreach ($this->ordens as $ordem) {
                //dd($ordem);
                $ordemBanco = Ordens::where('ativo_id', $ordem['ativo_id'])
                    ->where('corretora_id', $ordem['corretora_id'])
                    ->where('quantidade', $ordem['quantidade'])
                    ->where('carteira_id', $ordem['carteira_id'])
                    ->where('tipoordem', $ordem['tipoordem'])
                    ->where('data', $ordem['data'])
                    ->where('ativo_id', $ordem['ativo_id'])
                    ->get();

                if (sizeof($ordemBanco) == 0) {

                    DB::table('ordens')->insert($ordem);
                    //echo "Insert Data".$ordem['data']." - Tipo".$ordem['tipoordem']." - ".$ordem['carteira_id']." - ".$ordem['ativo_id']." <br>";
                }
            }

        }
    }


    public function getNotasCorretagem()
    {
        if (isset($this->corretora) && $this->corretora != null) {
            $this->directory .= "/" . strtolower(str_replace(' ','',$this->corretora->nome));
        }
        $files = Storage::disk('local')->allfiles($this->directory);
        $notasCorretagens = [];
        foreach ($files as $file) {

            if (Storage::mimeType($file) == "application/pdf") {

                $notasCorretagens[] = $file;
            }

        }

        $this->files = $notasCorretagens;
    }

    public function getConteudoPdf($file)
    {
        $parser = new PdfParser();
        $pdf = $parser->parseContent(Storage::get($file));
        return $pdf->getPages();

    }

    public function index(Request $request)
    {

        //abrir a pasta do usuario
        $files = Storage::disk('local')->allfiles($this->directory);
        $notas = [];

        foreach ($files as $file) {

            if (Storage::mimeType($file) == "application/pdf") {
                //$content = Storage::get($file);
                $parser = new PdfParser();
                $pdf = $parser->parseContent(Storage::get($file));
                $nota = [];

                foreach ($pdf->getPages() as $page) {
                    //dd($page->getText());
                    //dd($page->getTextArray());
                    //echo nl2br($page->getText());
                    $lines = explode("\n", $page->getText());
                    //dd($lines);
                    $nota = [
                        'data' => $this->getDataPregao($lines),
                        'ordens' => $this->getOrdens($lines,null),
                    ];

                }
                $notas[] = $nota;
            }

        }
        //notas importadas
        dd($notas);
    }

    public function getOrdens($lines, $p_ordens)
    {
        $strStart = "Praça";
        $strStop = "Resumo dos Negócios";
        $ordens = [];
        $start = false;
        foreach ($lines as $i => $line) {
            $linha = str_replace('/\t\t+/', '', $line);
            $linha = str_replace('\t', '', $line);
            $linha = preg_replace('/\s\s+/', ' ', $linha);
            if (strpos($line, $strStart) !== false) {
                $start = true;
                continue;
            }
            if (strpos($line, $strStop) !== false) {
                $start = false;
                break;
            }
            if ($start) {
                $itens = explode("\t", $line);
                if (strpos($line, 'Sub') === false) {
                    foreach ($itens as $i => $item) {
                        $itens[$i] = trim($item);
                        if (trim($item) == "") {
                            unset($itens[$i]);
                        }
                    }
                    $itens = array_values($itens);
                    $ordens[] = [
                        'origem' => isset($itens[0]) ? $itens[0] : '',
                        'tipoordem' => isset($itens[1]) ? $itens[1] : '',
                        'vis' => isset($itens[2]) ? $itens[2] : '',
                        'ticker' => isset(explode(' ', $itens[3])[0]) ? trim(explode(' ', $itens[3])[0]) : '',
                        'nome' => isset($itens[3]) ? $itens[3] : '',
                        'qtd' => isset($itens[4]) ? $itens[4] : 0.0,
                        'preco' => isset($itens[5]) ? $itens[5] : '',
                        'total' => isset($itens[6]) ? $itens[6] : '',
                        'tipodespesa' => isset($itens[7]) ? $itens[7] : '',
                    ];
                }
            }
        }
        return $ordens;

    }

    public function getDataPregao($lines)
    {
        $str = "Data pregão:";
        foreach ($lines as $line) {
            if (strpos($line, $str) !== false) {
                $valor = trim(str_replace($str, "", $line));
                return explode("\t", $valor)[0];
            }
        }

    }

    /**
     *
     * Recebe um array com os valor e a espressão a procurar
     *
     * @param [type] $valores
     * @param [type] $str
     * @return void
     */
    public function getValor($valores, $str)
    {
        $valor = "";
        foreach ($valores as $value) {

            if (strpos($value, $str) !== false) {
                return trim(str_replace($str, "", $value));
            }

        }
        return $valor;
    }

    public function definirCategoria($ticker)
    {

        $categoria = 'Ações';

        if (strlen($ticker) == 6) {
            $categoria = 'Fundos Imobiliário';
        }
        return $categoria;
    }
}
