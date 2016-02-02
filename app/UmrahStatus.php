<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UmrahStatus extends Model
{
    protected $fillable = ['status'];

    public function umrahs()
    {
        return $this->hasMany('App\Umrah');
    }
}
