<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ordens extends Model
{
    protected $fillable = ['carteira_id', 'corretora_id', 'ativo_id', 'tipoordem', 'data',
        'quantidade', 'preco', 'total',
        'despesas', 'outras_despesas',
        'split_id','split_data','split_quantidade_origem','split_valor_origem',
        'inplit_id','inplit_data','inplit_quantidade_origem','inplit_valor_origem', 'saldo',
        'origem','path'
    ];

    public function ativo()
    {
        return $this->belongsTo('App\Models\Ativos');
    }

    public function corretora()
    {
        return $this->belongsTo('App\Models\Corretoras');
    }

    public function carteira()
    {
        return $this->belongsTo('App\Models\Carteira');
    }

}
