<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    //
    protected $fillable = ['ward','constituency_id'];
    public function constituency()
    {
    	return $this->belongsTo('App\Constituency');
    }

     public function members()
    {
    	return $this->hasMany('App\Member','ward_id');
    }


}
