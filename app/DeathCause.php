<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeathCause extends Model
{
    protected $fillable = ['name'];

    protected $casts = ['id'    =>  'integer'];

    protected $hidden = ['created_at', 'updated_at'];

    public function deceased()
    {
        return $this->hasMany('App\Deceased');
    }
}
