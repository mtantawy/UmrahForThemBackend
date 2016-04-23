<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UmrahStatus extends Model
{
    protected $fillable = ['status'];

    protected $casts = [
        'id'                =>  'integer',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function umrahs()
    {
        return $this->hasMany('App\Umrah');
    }
}
