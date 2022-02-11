<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarteiraAnualIrpf extends Model
{
    //carteiraanualirpf
    protected $table = 'carteiraanualirpf';
    protected $fillable = ['ano','user_id','ativo_id','quantidade','precomedio','total'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function ativo()
    {
        return $this->belongsTo('App\Models\Ativos', 'ativo_id');
    }
}
