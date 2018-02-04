<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    //
    protected $fillable = ['member_number','skill'];

    public function members()
    {
    	return $this->belongsTo('App\Member','member_registration_number');
    }
}
