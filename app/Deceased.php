<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deceased extends Model
{
    const DONE_UMRAH_BEFORE_TRUE = 1;
    const DONE_UMRAH_BEFORE_FALSE = 0;
    const DONE_UMRAH_BEFORE_DONTKNOW = 2;

    protected $table = 'deceased';
    protected $casts = [
        'age'       =>  'integer',
        'user_id'   =>  'integer',
        'id'        =>  'integer',
        'death_cause_id'        =>  'integer',
        'done_umrah_before' =>  'integer',
    ];
    protected $fillable = [
        'name',
        'sex',
        'age',
        'country',
        'city',
        'death_cause',
        'death_date',
        'user_id',
        'done_umrah_before',
        'death_cause_id',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function umrahs()
    {
        return $this->hasMany('App\Umrah');
    }

    public function deathCauseObject()
    {
        return $this->belongsTo('App\DeathCause', 'death_cause_id');
    }
}
