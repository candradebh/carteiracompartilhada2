<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exchange extends Model
{
    protected $table = 'exchanges';

    protected $fillable = ['carteira_id', 'corretora_id', 'user_id',
        'data','origem', 'reais','dolar', 'taxas', 'cotacao'
    ];


    public function corretora()
    {
        return $this->belongsTo('App\Models\Corretoras');
    }

    public function carteira()
    {
        return $this->belongsTo('App\Models\Carteira');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
