<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotacoes extends Model
{
    protected $fillable = ['ativo_id','data','open', 'close','higth','low','volume'];

    public function ativo()
    {
        return $this->belongsTo('App\Models\Ativos','ativo_id');
    }

}
