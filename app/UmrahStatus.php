<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UmrahStatus extends Model
{
    protected $fillable = ['status'];

    protected $casts = [
        'id'                =>  'integer',
    ];

    public function umrahs()
    {
        return $this->hasMany('App\Umrah');
    }
}
