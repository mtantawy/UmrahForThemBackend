<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Umrah extends Model
{
    protected $fillable = ['user_id', 'deceased_id', 'umrah_status_id'];

    public function umrahStatus()
    {
        return $this->belongsTo('App\UmrahStatus');
    }

    public function deceased()
    {
        return $this->belongsTo('App\Deceased');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
