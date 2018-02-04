<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Userskill extends Model
{
    //
    protected $fillable = ['user_id','skill'];
    public function user()
    {
    	return $this->belongsTo('App\User');
    }
}
