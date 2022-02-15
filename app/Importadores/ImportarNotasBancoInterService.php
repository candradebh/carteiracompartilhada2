<?php

namespace App\Importadores;


use Smalot\PdfParser\Parser as PdfParser;

class ImportarNotasBancoInterService extends ImportarNotasService
{

    public function lerNotas()
    {

        //dd($this->files);
        foreach ($this->files as $file) {

            $path = base_path() . "\\storage\\app\\" . implode("\\", explode("/", $file));

            if (mime_content_type($path) == "application/pdf") {

                $parser = new PdfParser();
                $pdf = $parser->parseContent(file_get_contents($path));

                $ordensImportadas = [];
                $nota = [];
                //$text = $pdf->getText();
                foreach ($pdf->getPages() as $page) {
                    //dd($page->getText());
                    //dd($page->getTextArray());
                    //echo nl2br($page->getText());
                    $lines = explode("\n", $page->getText());
                    $ordensImportadas = $this->getOrdens($lines, $ordensImportadas);
                    //dd($ordensImportadas);
                    $nota = [
                        'data' => $this->getDataPregao($lines),
                        'resumo' => $this->getRodape($lines),
                        'path' => $file,
                    ];
                    //dd($path);

                }
                $nota['ordens'] = $ordensImportadas;
                $notas[] = $nota;
            }

            $this->notas[] = $nota;
        }
        //notas importadas
        //dd($this->notas);
    }

    public function getOrdens($lines, $p_ordens)
    {

        $strStart = "Praça";
        $strStop = "Resumo dos Negócios";
        $ordens = $p_ordens;
        //dd($ordens);
        $start = false;
        //dd($lines);

        $numOrdem = 0;
        foreach ($lines as $n => $line) {

            //padrao de pegar as colunas
            $indices = [
                'tipo' => 1,
                'mercado' => 2,
                'ticker' => 3,
                'qtd' => 4,
                'preco' => 5,
                'total' => 6,
                'tipodespesa' => 7,
            ];

            $linha = str_replace('/\t\t+/', '', $line);
            //$linha = str_replace('\t', '', $linha);
            //$linha = preg_replace('/\s\s+/', ' ', $linha);
            //echo $i;
            //echo var_dump($linha)."<br>";

            if (strpos(trim($linha), $strStart) !== false) {
                $start = true;
                //echo "$strStart<br>";
                //echo "Start $start <br> ";
                continue;
            }


            if (strpos(trim($linha), $strStop) !== false) {
                //echo "Str Stop ". $strStop. "<br>";
                $start = false;
                //echo "Start". boolval($start). "<br> ";
                //dd($start);
                //dd($linha);
                break;
            }

            //echo "Start $start<Br>";
            if ($start === true) {



                    if ($start === true && strstr($linha, 'Sub', true) === false) {
                        //dd(strstr($linha, 'SubTotal',true) );
                        //var_dump(trim($linha));
                        $itens = explode("\t", $linha);

                        foreach ($itens as $i => $item) {
                            $itens[$i] = trim($item);
                            if (trim($item) == "") {
                                unset($itens[$i]);
                            }
                        }

                        //echo "$n<br>";
                        //dd($itens);

                        $itens = array_values($itens);

                        //tipo da ordem
                        if (isset($itens[$indices['tipo']]) && (strtoupper($itens[$indices['tipo']]) == "C" || strtoupper($itens[$indices['tipo']]) == "V")) {
                            $tipoordem = $itens[$indices['tipo']];
                        }

                        //tipo da ordem
                        if (isset($itens[$indices['ticker']])) {

                            $str = $itens[$indices['ticker']];
                            $nomeTicker = trim($str);
                            $str = str_replace(' ', '', trim($str));
                            $tickerAux = substr($str, 0, 6);
                            $abrev = substr($tickerAux, 4, 6);

                            //var_dump(is_numeric($abrev));

                            //dd(is_numeric(substr($abrev,1,1)));
                            if (!is_numeric($abrev)) {
                                $ticker = substr($tickerAux, 0, 5);
                                //if($n==17) dd($abrev);
                            } else {
                                $ticker = $tickerAux;
                                //dd($abrev);
                            }

                        }

                        if (isset($itens[$indices['qtd']]) && intval($itens[$indices['qtd']]) == null) {

                            foreach ($indices as $k => $v) {
                                $indices[$k] = $v + 1;
                            }
                        }

                        //dd($indices);
                        $ordens[] = [
                            'corretora_id' => $this->corretora->id,
                            'titulo' => isset($itens[0]) ? $itens[0] : '',
                            'tipoordem' => $tipoordem,
                            'vis' => isset($itens[2]) ? $itens[2] : '',
                            'ticker' => $ticker,
                            'nome' => $nomeTicker,
                            'qtd' => isset($itens[$indices['qtd']]) ? str_replace(' ', '', $itens[$indices['qtd']]) : 0.0,
                            'preco' => isset($itens[$indices['preco']]) ? str_replace(' ', '', $itens[$indices['preco']]) : 0.0,
                            'total' => isset($itens[$indices['total']]) ? str_replace(' ', '', $itens[$indices['total']]) : 0.0,
                            'corretagem' => 0.0,
                            'outras_despesas' => 0.0,
                            'tipodespesa' => isset($itens[$indices['tipodespesa']]) ? str_replace(' ', '', $itens[$indices['tipodespesa']]) : '',
                            'origem' => 'ARQUIVO',
                        ];
                        //dd($ordens);

                        $indices = [
                            'tipo' => 1,
                            'mercado' => 2,
                            'ticker' => 3,
                            'qtd' => 4,
                            'preco' => 5,
                            'total' => 6,
                            'tipodespesa' => 7,
                        ];

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
        $strStart = "Resumo dos Negócios";
        $strIgnorar = "Resumo Financeiro";
        $strStop = "Especificações diversas";
        $ordens = [];
        $start = false;
        foreach ($lines as $i => $line) {
            $linha = str_replace('/\t\t+/', '', $line);
            $linha = str_replace('\t', '', $linha);
            //echo $linha."<br>";
            $linha = preg_replace('/\s\s+/', ' ', $linha);

            //echo $linha."<br>";
            if (strpos(trim($line), trim($strStart)) !== false) {
                $start = true;
                //echo $strStart;
                continue;
            }

            if ($start == true) {
                $itens = explode("\t", $line);

                foreach ($itens as $i => $item) {
                    $itens[$i] = trim($item);
                    if (trim($item) == "") {
                        unset($itens[$i]);
                    }
                }
                $itens = array_values($itens);
                //dd($itens);

                if ($itens[0] == "Debêntures") {
                    $ordens['debentures'] = isset($itens[1]) ? $itens[1] : 0.0;
                } elseif ($itens[0] == "Vendas à V ista" || $itens[0] == "Vendas à Vista") {
                    $ordens['vendasavista'] = isset($itens[1]) ? $itens[1] : 0.0;
                } elseif ($itens[0] == "Compras à V ista" || $itens[0] == "Compras à Vista") {
                    $ordens['comprasavista'] = isset($itens[1]) ? $itens[1] : 0.0;
                } elseif ($itens[0] == "Opções - Compras") {
                    $ordens['opcoescompra'] = isset($itens[1]) ? $itens[1] : 0.0;
                } elseif ($itens[0] == "Opções - Vendas") {
                    $ordens['opcoesvendas'] = isset($itens[1]) ? $itens[1] : 0.0;
                } elseif ($itens[0] == "Operações a T ermo" || $itens[0] == "Operações a Termo") {
                    $ordens['operacoesatermo'] = isset($itens[1]) ? $itens[1] : 0.0;
                } elseif ($itens[0] == "Operações a Futuro") {
                    $ordens['operacoesfuturo'] = isset($itens[1]) ? $itens[1] : 0.0;
                } elseif ($itens[0] == "Valor das Oper . com Tit. Publ.") {
                    $ordens['operacoestitulopublico'] = isset($itens[1]) ? $itens[1] : 0.0;
                } elseif ($itens[0] == "Valor das Operações") {
                    $ordens['valoroperacoes'] = isset($itens[1]) ? $itens[1] : 0.0;
                } elseif ($itens[0] == "Valor do Ajuste p/Futuro") {
                    $ordens['valorajustefuturo'] = isset($itens[1]) ? $itens[1] : 0.0;
                } elseif ($itens[0] == "IR Sobre Corretagem") {
                    $ordens['irsobrecorretagem'] = isset($itens[1]) ? $itens[1] : 0.0;
                } elseif ($itens[0] == "IRRF Sobre Day T rade" || $itens[0] == "IRRF Sobre Day Trade") {
                    $ordens['irsobredaytrade'] = isset($itens[1]) ? $itens[1] : 0.0;
                } elseif ($itens[0] == $strIgnorar) {
                    continue;
                } elseif ($itens[0] == "Valor Líquido das Operações(1)") {
                    $ordens['valorLiquidoSobreOperacoes'] = [
                        'valor' => isset($itens[1]) ? $itens[1] : 0.0,
                        'tipo' => isset($itens[2]) ? $itens[2] : "",
                    ];
                } elseif ($itens[0] == "Taxa de Liquidação(2)") {
                    $ordens['taxaLiquidacao'] = [
                        'valor' => isset($itens[1]) ? $itens[1] : 0.0,
                        'tipo' => isset($itens[2]) ? $itens[2] : "",
                    ];
                } elseif ($itens[0] == "Taxa de Registro(3)") {
                    $ordens['taxaRegistro'] = [
                        'valor' => isset($itens[1]) ? $itens[1] : 0.0,
                        'tipo' => isset($itens[2]) ? $itens[2] : "",
                    ];
                } elseif ($itens[0] == "Total(1+2+3) A") {
                    $ordens['total123A'] = [
                        'valor' => isset($itens[1]) ? $itens[1] : 0.0,
                        'tipo' => isset($itens[2]) ? $itens[2] : "",
                    ];
                } elseif ($itens[0] == "Taxa de T ermo/Opções/Futuro" || $itens[0] == "Taxa de Termo/Opções/Futuro") {
                    $ordens['taxaTermoOpcoesFuturos'] = [
                        'valor' => isset($itens[1]) ? $itens[1] : 0.0,
                        'tipo' => isset($itens[2]) ? $itens[2] : "",
                    ];
                } elseif ($itens[0] == "Taxa A.N.A") {
                    $ordens['taxaAna'] = [
                        'valor' => isset($itens[1]) ? $itens[1] : 0.0,
                        'tipo' => isset($itens[2]) ? $itens[2] : "",
                    ];
                } elseif ($itens[0] == "Emolumentos") {
                    $ordens['emolumentos'] = [
                        'valor' => isset($itens[1]) ? $itens[1] : 0.0,
                        'tipo' => isset($itens[2]) ? $itens[2] : "",
                    ];
                } elseif ($itens[0] == "Total Bolsa B") {
                    $ordens['totalBolsaB'] = [
                        'valor' => isset($itens[1]) ? $itens[1] : 0.0,
                        'tipo' => isset($itens[2]) ? $itens[2] : "",
                    ];
                } elseif ($itens[0] == "Corretagem") {
                    $ordens['corretagem'] = [
                        'valor' => isset($itens[1]) ? $itens[1] : 0.0,
                        'tipo' => isset($itens[2]) ? $itens[2] : "",
                    ];
                } elseif ($itens[0] == "ISS") {
                    $ordens['iss'] = [
                        'valor' => isset($itens[1]) ? $itens[1] : 0.0,
                        'tipo' => isset($itens[2]) ? $itens[2] : "",
                    ];
                } elseif ($itens[0] == "I.R.R.F . s/ operações, base 0,00") {
                    $ordens['irrfOperacoesBase'] = [
                        'valor' => isset($itens[1]) ? $itens[1] : 0.0,
                        'tipo' => isset($itens[2]) ? $itens[2] : "",
                    ];
                } elseif ($itens[0] == "Outras") {
                    $ordens['outras'] = [
                        'valor' => isset($itens[1]) ? $itens[1] : 0.0,
                        'tipo' => isset($itens[2]) ? $itens[2] : "",
                    ];
                } elseif ($itens[0] == "Outras") {
                    $ordens['outras'] = [
                        'valor' => isset($itens[1]) ? $itens[1] : 0.0,
                        'tipo' => isset($itens[2]) ? $itens[2] : "",
                    ];
                } else {
                    $ordens['liquido'] = [
                        'valor' => isset($itens[1]) ? $itens[1] : 0.0,
                        'tipo' => isset($itens[2]) ? $itens[2] : "",
                    ];
                    //dd($itens);
                }

            }
        }

        //dd($ordens);
        return $ordens;

    }

}
