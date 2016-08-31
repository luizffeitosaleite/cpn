<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = [
        'data', 'version', 'net_id', 'author',
    ];

    public function net(){
        return $this->belongsTo('App\Net', 'net_id', 'id');
    }
}
