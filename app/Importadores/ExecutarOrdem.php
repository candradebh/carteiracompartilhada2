<?php

namespace App\Importadores;

use App\Models\Carteira;
use App\Models\Ordens;
use Illuminate\Http\Request;

class ExecutarOrdem
{

    public $ordem;
    public $ativo;
    public $carteira;
    public $resultado;
    public $saldo;
    public $novoSaldo;
    public $acumuladoCarteira;
    public $totalacumulado;

    /**
     * Create a new para executar instance.
     *
     * @return void
     */

    public function __construct($data)
    {
        $this->ordem = Ordens::create($data);
        $this->carteira = Carteira::where('id',$this->ordem->carteira_id)->with('ativos')->first();
        $this->ativo = $this->carteira->ativos()->wherePivot('ativo_id', $this->ordem->ativo_id)->first();
        $this->resultado = 0.0;
        $this->saldoVenda = 0.0;
        $this->novoSaldo = 0.0;
        $this->totalacumulado =0.0;

        $this->acumuladoCarteira  = 0.0;
        $this->quantidadeCarteira  = 0.0;
        $this->totalCarteira  = 0.0;
    }


    /**
     *
     *  @return void
     */
    public function executar()
    {
        if($this->ativo == null){
            $this->carteira->ativos()->attach($this->ordem->ativo_id);
            $this->ativo = $this->carteira->ativos()->wherePivot('ativo_id', $this->ordem->ativo_id)->first();
        }

        $this->quantidadeCarteira = $this->ativo->pivot->quantidade;
        $this->totalCarteira = $this->ativo->pivot->total;
        $this->acumuladoCarteira = $this->ativo->pivot->totalacumulado;

        //adicionar o ativo na carteira se existir
        if(strtoupper($this->ordem->tipoordem) == "C" ){
            $this->compra();
        }

        if(strtoupper($this->ordem->tipoordem) == "V" ){
            $this->venda();
        }

        $this->ordem->update(['saldo'=>$this->saldoVenda<0?$this->saldoVenda*-1:$this->saldoVenda]);

        $this->calcularTotal();

        //dd($this);
    }



    public function compra()
    {
        //busco todas as ordens de venda para realizar a compra se estiver operando vendido
        $minhasOrdensVenda = Ordens::where('carteira_id',$this->ordem->carteira_id)
                                    ->where('ativo_id',$this->ordem->ativo_id)
                                    ->where('tipoordem','V')
                                    ->where('saldo','<>',0)
                                    ->where('data','<=',$this->ordem->data)
                                    ->orderBy('data','asc')->get();

        $this->saldoVenda = $this->ordem->quantidade;
        foreach($minhasOrdensVenda as $mov){
            $this->saldoVenda -= $mov->saldo;

            if($this->saldoVenda < 0){
                $this->novoSaldo = 0;
            }else{
                $this->novoSaldo = $this->saldoVenda;
            }
            //atualiza a posição de vendido
            $mov->update(['saldo'=>$this->novoSaldo]);
            if($this->saldoVenda>=0){
                break; //A venda foi concluida totalmente
            }
        }

        $this->quantidadeCarteira += $this->ordem->quantidade;
        $this->totalCarteira += $this->ordem->total;


    }

    public function venda()
    {
        $dataordemFormatada = date('Y-m-d',strtotime($this->ordem->data));

        //Abatendo nas ordens para definir o lucro em cada venda e incrementar o acumulado do ativo na carteira
        $minhasOrdens = Ordens::where('carteira_id',$this->ordem->carteira_id)
                                        ->where('ativo_id',$this->ordem->ativoid)
                                        ->where('tipoordem','C')
                                        ->where('saldo','<>',0)
                                        ->where('data','<=',$this->ordem->data)
                                        ->orderBy('data','asc')->get();
        //dd($minhasOrdens);
        $this->saldoVenda = - $this->ordem->quantidade;
        foreach($minhasOrdens as $mo){
            $despesasDaOrdem = $mo->despesas + $mo->outras_despesas;
            $this->saldoVenda = $mo->saldo + ( $this->saldoVenda );
            if($this->saldoVenda < 0){
                $this->novoSaldo = 0;
            }else{
                $this->novoSaldo = $this->saldoVenda;
            }

            $this->totalCarteira = $this->totalCarteira - (($this->novoSaldo == 0 ? $mo->quantidade : $mo->quantidade - $this->novoSaldo) * $mo->preco  + $despesasDaOrdem );

            //calcular o lucro ou prejuizo nessa operacao
            $valorAcumuladoDaOrdem = 0;
            if($this->saldoVenda <= 0){
                //falta vender mais em outras ordens
                $valorAcumuladoDaOrdem =($mo->quantidade * $this->ordem->preco)  - ($mo->quantidade * $mo->preco);
            }else {
                $valorAcumuladoDaOrdem = (($mo->quantidade - $this->saldoVenda) * $this->ordem->preco) - (($mo->quantidade - $this->saldoVenda) * $mo->preco );
            }

            $this->totalacumulado = $this->totalacumulado + ($valorAcumuladoDaOrdem);


            $mo->update(['saldo'=>$this->novoSaldo]);

            if($this->saldoVenda>=0){
                $this->saldoVenda = 0;
                break; //A venda foi concluida totalmente
            }
        }//for das vendas

        $this->quantidadeCarteira = $this->quantidadeCarteira - $this->ordem->quantidade;
        $this->totalCarteira = $this->totalCarteira - $this->ordem->total;

    }

    public function calcularTotal(){

        $ordersDoAtivo = Ordens::where('carteira_id',$this->ordem->carteira_id)
                                ->where('ativo_id',$this->ordem->ativo_id)
                                ->where('saldo','<>',0)
                                ->where('data','<=',$this->ordem->data)
                                ->get();

        $qtdAtivo = 0;
        $totalAtivo = 0;
        foreach($ordersDoAtivo as $orderAtivo){
            if(strtoupper($orderAtivo->tipoordem) == "V" ){
                $qtdAtivo-=  $orderAtivo->saldo;
                $totalAtivo-=  $orderAtivo->saldo *  $orderAtivo->preco + ( $orderAtivo->despesas +  $orderAtivo->outras_despesas);
            }else{
                $qtdAtivo+=  $orderAtivo->saldo;
                $totalAtivo+=  $orderAtivo->saldo *  $orderAtivo->preco + ( $orderAtivo->despesas +  $orderAtivo->outras_despesas);
            }

        }

        $this->ativo->pivot->update([ 'quantidade'=>$qtdAtivo,
            'total'=>$totalAtivo,
            'totalacumulado'=>$this->acumuladoCarteira + ( $this->totalacumulado)
        ]);

    }
}
