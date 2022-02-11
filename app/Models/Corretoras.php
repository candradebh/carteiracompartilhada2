<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Corretoras extends Model
{
    protected $fillable = ['nome', 'realizaimportacao'];

    public function ordens()
    {
        return $this->hasMany('App\Models\Ordens');
    }
}
