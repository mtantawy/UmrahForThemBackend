<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deceased extends Model
{
    protected $table = 'deceased';
    protected $fillable = ['name', 'sex', 'age', 'country', 'city', 'death_cause', 'death_date'];
}
