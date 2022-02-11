<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resultados extends Model
{
    protected $table = 'resultados';

    protected $fillable = [
        'user_id','tipoativo', 'ano', 'mes', 'compras', 'vendas', 'resultado',
        'despesas', 'patrimonio','darf', 'prejuizoacumulado'
    ];


    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

}
