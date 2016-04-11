<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Umrah extends Model
{
    protected $fillable = ['user_id', 'deceased_id', 'umrah_status_id'];

    protected $casts = [
        'id'                =>  'integer',
        'user_id'           =>  'integer',
        'deceased_id'       =>  'integer',
        'umrah_status_id'   =>  'integer',
    ];

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
