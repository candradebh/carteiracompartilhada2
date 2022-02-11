<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtivosCarteiras extends Model
{
    protected $table = 'ativos_carteiras';
    protected $fillable = ['ativo_id','carteira_id','quantidade','total','totalacumulado'];

    public function carteiras()
    {
        return $this->belongsTo('App\Models\Carteira', 'carteira_id');
    }

    public function ativos()
    {
        return $this->belongsTo('App\Models\Ativos', 'ativo_id');
    }



}
