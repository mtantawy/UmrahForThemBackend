<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deceased extends Model
{
    protected $table = 'deceased';
    protected $casts = [
        'age'       =>  'integer',
        'user_id'   =>  'integer',
        'id'        =>  'integer',
        'done_umrah_before' =>  'boolean',
    ];
    protected $fillable = ['name', 'sex', 'age', 'country', 'city', 'death_cause', 'death_date', 'user_id'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function umrahs()
    {
        return $this->hasMany('App\Umrah');
    }
}
