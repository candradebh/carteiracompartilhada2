<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carteira extends Model
{
    protected $fillable = [
        'user_id','nome', 'descricao', 'total','tipomoeda'
    ];

    public function ativos()
    {
        return $this->belongsToMany('App\Models\Ativos', 'ativos_carteiras', 'carteira_id', 'ativo_id')->withPivot('quantidade', 'precomedio' ,'total', 'totalacumulado', 'carteira_id','ativo_id');
    }

    public function ordens()
    {
        return $this->hasMany('App\Models\Ordens', 'carteira_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }



    public function usuariosCompartilhados()
    {
        return $this->belongsToMany('App\User', 'carteiras_users', 'carteira_id', 'user_id')->withPivot('datafinal','status');
    }
}
