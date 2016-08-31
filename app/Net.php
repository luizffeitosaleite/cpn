<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Net extends Model
{
    protected $fillable = [
        'name', 'owner',
    ];

    public function author(){
        return $this->belongsTo('App\User', 'owner','id');
    }

    public function states(){
        return $this->hasMany('App\State', 'net_id', 'id');
    }

}
