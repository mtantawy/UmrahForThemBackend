<?php

namespace app;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'sex', 'date_of_birth', 'country', 'city'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'created_at', 'updated_at'];

    public function deceased()
    {
        return $this->hasMany('App\Deceased');
    }

    public function umrahs()
    {
        return $this->hasMany('App\Umrah');
    }

    public function getUmrahRequestsCount()
    {
        return $this->umrahs->count();
    }

    public function getUmrahsCountByStatusID($umrah_status_id)
    {
        return \App\Umrah::where('user_id', $this->id)
                        ->where('umrah_status_id', $umrah_status_id)
                        ->count();
    }
}
