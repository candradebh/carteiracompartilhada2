<?php

namespace App\Importadores;


use App\Models\Ativos;
use Smalot\PdfParser\Parser as PdfParser;

class ImportarNotasBancoXpService extends ImportarNotasService
{

    public function lerNotas()
    {

        foreach ($this->files as $file) {

            $path = base_path() . "\\storage\\app\\" . implode("\\", explode("/", $file));

            if (mime_content_type($path) == "application/pdf") {

                $parser = new PdfParser();
                $pdf = $parser->parseContent(file_get_contents($path));

                $ordensImportadas = [];
                $resumo = [];
                $nota = [];
                //$text = $pdf->getText();

                foreach ($pdf->getPages() as $p => $page) {
                    //dd($page->getText());
                    //dd($page->getTextArray());
                    //echo nl2br($page->getText());
                    $lines = explode("\n", $page->getText());
                    $dataNota = $this->getDataPregao($lines);

                    //if ($dataNota == "31/05/2019") {
                        //$ordensImportadas = $this->getOrdens($lines, $ordensImportadas);
                        //dd($ordensImportadas);
                   // }
                    $ordensImportadas = $this->getOrdens($lines, $ordensImportadas);

                    if ($p == (sizeof($pdf->getPages()) - 1)) {
                        $resumo = $this->getRodape($lines);
                    }

                    $nota = [
                        'data' => $dataNota,
                        'path' => $file,
                    ];
                }
                $nota['ordens'] = $ordensImportadas;
                $nota['resumo'] = $resumo;
                $notas[] = $nota;
            }
            //dd($nota);
            $this->notas[] = $nota;
        }
        //notas importadas
        //dd($this->notas);
    }

    public function getOrdens($lines, $p_ordens)
    {
        $itemDebugar = false;
        $ordens = $p_ordens;
        $linhaOrdem = false;
        $itens = [];
        $item = 0;
        for ($i = 0; $i < sizeof($lines); $i++) {

            $linha = $this->normalizaLinha($lines[$i]);
            echo "<br>linha = ".$linha."";

            //encontrei a linha das ordens com valores    
            if ($linha == "1-BOVESPA") {
                $item++;
                $linhaOrdem = true;
                $itens[] = $linha;
                continue;
            }

            //aqui eu navego na proxima informacao     
            if ($linhaOrdem) {
                
                echo "<br>item = $item | linhadaordem = $linhaOrdem | sizeof = ".sizeof($itens)." | <br>";
                if ($linha == "BANCOPANPN") {
                    $itemDebugar = true;
                }
                //tipo da ordem V ou C
                if ($item == 1 && (substr($linha, 0, 1) == "C" || substr($linha, 0, 1) == "V")) {
                    $tipo = substr($linha, 0, 1);
                    $itens[] = $tipo;
                    $item++;
                    continue;
                }

                //ticker
                if ($item == 2) {
                    $itens[] = $linha;
                    $item++;
                    continue;
                }

                //NM ou EJ ou bla
                if ($item == 3) {
                    $itens[] = $linha;
                    $item++;
                    continue;
                }

                //quantidade e preco
                if ($item == 4 || $item == 5) {

                    $valores = explode(" ", $lines[$i]);
                    
                    foreach($valores as $valor){

                        $valor = str_replace(",", ".", str_replace(".", "", $valor));

                        $tipoValor = $this->tipoValor($valor);
                        

                        if ($tipoValor == "string" || ((float) $valor)==0) {
                            continue;
                        }
                        
                        echo "Item: $item = $valor ($tipoValor) = ".((float) $valor)."<br>" ;

                        if ($tipoValor != "string" && ((float) $valor)!=0) {
                            $itens[] = (float) $valor;
                            $item++;
                        }
                        echo "Final Item: $item <br>" ;
                        if($item == 6)
                            break;

                    }
                    continue;
                }

                //total
                if($item == 6){
                    
                    $valor = str_replace(",", ".", str_replace(".", "", $linha));
                    $tipoValor = $this->tipoValor($valor);
                    if($tipoValor!="string"){
                        $itens[] = (float) $valor;
                        $item++;
                        continue;
                    }

                }

                if ($item == 7) {
                    
                    $op = substr($linha, 0, 1);
                    $itens[] = $op;
                    $item++;
                    
                }
                
                //montar a ordem
                if ($item == 8) {
                    
                    
                    
                    if (isset($itens[2]) && isset($itens[4]) && isset($itens[5]) && isset($itens[6])) {

                        $ordens[] = [
                            'corretora_id' => $this->corretora->id,
                            'titulo' => isset($itens[0]) ? $itens[0] : '',
                            'tipoordem' => isset($itens[1]) ? $itens[1] : '',
                            //'vis' => isset($itens[2]) ? $itens[2] : '',
                            'ticker' => isset($itens[2]) ? $itens[2] : '',
                            'nome' => isset($itens[2]) ? $itens[2] : '',
                            'qtd' => isset($itens[4]) ? $itens[4] : 0.0,
                            'preco' => isset($itens[5]) ? $itens[5] : 0.0,
                            'total' => isset($itens[6]) ? $itens[6] : 0.0,
                            'corretagem' => 0.0,
                            'outras_despesas' => 0.0,
                            'tipodespesa' => isset($itens[7]) ? $itens[7] : '',
                            'origem' => 'ARQUIVO',
                        ];

                        $linhaOrdem = false;
                        $item = 0;
                        $itens = [];
                    }
                   
                }
            }
        }

        return $ordens;
    }

    public function getDataPregao($lines)
    {
        $titulo = "Datapregão";
        $str = "";
        $valor = "";

        //dd($lines);

        foreach ($lines as $k => $line) {

            $linha = $this->normalizaLinha($line);

            //echo " $k - $linha <br>" ;
            if (strpos($linha, $titulo) !== false) {

                //xp
                if ($linha[0] == "1") {
                    $str = str_replace("1", "", $linha);
                }

                $str = str_replace($titulo, "", $linha);

                if ($str != "" && $str[0] == ":") {
                    //bancointer
                    $str = str_replace(":", "", $str);
                    $valor = explode("\t", $str)[0];
                } else {
                    //xp
                    $linha = $this->normalizaLinha($lines[$k + 1]);
                    $valor = $linha;
                }
            }
        }

        //dd($valor);

        return $valor;
    }

    public function normalizaLinha($line)
    {

        $linha = str_replace('/\t\t+/', '', $line);
        $linha = str_replace('\t', '', $linha);
        $linha = str_replace(' ', '', $linha);

        return $linha;
    }

    public function getRodape($lines)
    {
        $strStart = "TotalCustos/Despesas";
        $strIgnorar = "Resumo Financeiro";
        $strStop = "DResumoFinanceiro";
        $ordens = [];
        $itens = [];
        $start = false;
        //dd($lines);
        $valor = 0;
        foreach ($lines as $i => $line) {
            $linha = $this->normalizaLinha($line);


            //echo $linha."<br>";
            if (strpos(trim($linha), $strStart) !== false) {
                $start = true;
                //echo "Start Rodape -> $linha <br> ";
                continue;
            }
            if (strpos(trim($linha), $strStop) !== false) {
                break;
            }

            if ($start == true) {

                $numUs = str_replace(",", ".", str_replace(".", "", trim($linha)));
                //echo "Numero $i - $linha<br>";

                if (trim($linha) != "D" && is_numeric($numUs) == false) {
                    $itens[trim($linha)] = $valor;
                    $valor = 0;
                }
                if (is_numeric($numUs)) {
                    $valor = $numUs + 0;
                }
            }
        }

        //dd($itens);
        return $itens;
    }

    public function obterOrdensDasNotas()
    {

        foreach ($this->notas as $nota) {

            //dd($nota);
            $strData = $nota['data'];
            $strDataUs = implode('-', array_reverse(explode('/', $strData)));
            $dataOrdemBanco = date('Y-m-d H:i:s', strtotime($strDataUs . ' 12:00:00'));
            if ($dataOrdemBanco == "2019-05-31 12:00:00") {
                //dd($nota);
            }
            $i = 1;
            foreach ($nota['ordens'] as $ordem) {

                $tipo = "";
                if (str_contains($ordem['ticker'], 'FII')) {

                    $ticker = str_replace("FII", "", $ordem['ticker']);
                    $ticker = str_replace("CI", "", $ticker);
                    $ticker = substr($ticker, (strlen($ticker) - 6), strlen($ticker));
                    $tipo = "FII";
                } else {

                    if (str_contains($ordem['ticker'], 'ON')) {
                        $ticker = str_replace("ON", "", $ordem['ticker']);
                        $tipo = "ON";
                    }

                    if (str_contains($ordem['ticker'], 'PN')) {

                        $pos = strpos($ordem['ticker'], "PN");
                        $afixo = substr($ordem['ticker'], $pos, strlen($ordem['ticker']));
                        $tipo = $afixo;
                        $ticker = str_replace($afixo, "", $ordem['ticker']);
                    }

                    if (str_contains($ordem['ticker'], 'UNT')) {

                        $ticker = str_replace("UNT", "", $ordem['ticker']);
                        $tipo = "UNT";
                    }

                    $ticker = str_replace("S/A", "", $ticker);
                }


                $ativo = null;
                if ($tipo == "FII") {
                    $ativo = Ativos::where('ticker', $ticker)
                        ->orWhere('xpimport', $ordem['ticker'])
                        ->orWhere('xpimport', 'like', "%" . trim($ordem['ticker']) . "%")
                        ->first();
                } else {
                    $ativo = Ativos::where('nome', 'like', "$ticker%")
                        ->orWhere('xpimport', $ordem['ticker'])
                        ->orWhere('xpimport', 'like', "%" . trim($ordem['ticker']) . "%")
                        ->first();
                }


                $data = date('Y-m-d H:i:s', strtotime($dataOrdemBanco . "+$i minutes"));
                $i++;

                //crio o ativo se nao existe
                if ($ativo == null) {
                    $categoria = $this->definirCategoria($ordem['ticker']);
                    //dd($nota, $tipo,$ticker,$ordem);
                    $ativo = Ativos::create(['ticker' => $ticker, 'nome' => $ordem['nome'], 'setor' => '-', 'classe' => '-', 'categoria' => $categoria, 'cotacao' => 0.0, 'dataCotacao' => null, 'xp_import' => 'FIIBTLGBTLG11CI']);
                } else {

                    $ativo->xpimport = $ordem['ticker'];
                    $ativo->save();
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
    }

    public function tipoValor($valor)
    {
        if (is_numeric($valor)) {

            if (str_contains($valor, ".")) {
                $tipoValor = "float";
            } else {
                $tipoValor = "int";
            }
        } else {
            $tipoValor = "string";
        }

        return $tipoValor;
    }
}
