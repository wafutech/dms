<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Authenticatable
{
    use Notifiable;
   // use  CanResetPassword, EntrustUserTrait;
    use   EntrustUserTrait;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','activated','banned'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

     public function roles()
    {
        return $this->belongsToMany('App\Role');
    }
    public function UserProfile()
    {
        return $this->hasOne('App\Userprofile');
    }

    public function skills()
    {
        return $this->hasMany('App\Userskill');
    }
}
