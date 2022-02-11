<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ativos extends Model
{
    protected $fillable = ['ticker', 'nome', 'cnpj' ,'setor','classe','categoria','cotacao','dataCotacao','xpimport','dataAnalise','mm14','mm30','mm180','mm365','mm730'];

    public function ordens()
    {
        return $this->hasMany('App\Models\Ordens');
    }

    public function cotacoes()
    {
        return $this->hasMany('App\Models\Cotacoes','ativo_id');
    }

    public function carteiras()
    {
        return $this->belongsToMany('App\Models\Carteira', 'ativos_carteiras',  'ativo_id','carteira_id')->withPivot('quantidade','total','totalacumulado','carteira_id','ativo_id','tagprazo_id','tagintencao_id');

    }

}
