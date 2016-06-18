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
        'hide_performer_info'   =>  'boolean'
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
    protected $fillable = ['name', 'email', 'password', 'sex', 'country', 'city', 'hide_performer_info'];

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
        return $this->deceased->count();
    }

    public function getUmrahsCountByStatusID($umrah_status_id)
    {
        return \App\Umrah::where('user_id', $this->id)
                        ->where('umrah_status_id', $umrah_status_id)
                        ->count();
    }

    public function resetPassword()
    {
        $password = str_random(10);
        $this->password = bcrypt($password);

        try {
            $this->update();

            \Mail::send('emails.password_reset', ['name' => $this->name, 'password' => $password], function ($message) {
                $message->to($this->email, $this->name);
                $message->from('password_reset@umrah4them.com', 'Umrah4Them.com');
                $message->subject(trans('emails.password_reset_subject'));
                $message->replyTo('noreply@umrah4them.com', $name = null);
            });
            
        } catch (Exception $e) {
            \Log::error($e->getMessage());
            return false;
        } catch (ClientException $e) {
            \Log::error($e->getMessage());
            return false;
        }
    }
}
