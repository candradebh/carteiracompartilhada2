<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operacoes extends Model
{
    protected $fillable = ['ativo_id','tipooperacao','data','proporcao','valor_original', 'valor_alterado','novoticker', 'novonome','novocnpj'];

    public function ativo()
    {
        return $this->belongsTo('App\Models\Ativos','ativo_id');
    }

}
