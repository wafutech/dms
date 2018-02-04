<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserActivation extends Model
{
    //
    protected $table ='user_activations';
    //protected $fillable = '';

     public function user()
    {
        return $this->belongsTo('App\User');
    }
}
