<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Constituency extends Model
{
    //
    protected $table = 'constituencies';
    protected $fillable = ['const_name','county_id'];
    public function county()
    {
    	return $this->belongsTo('App\County');
    }
    public function wards()
    {
    	return $this->hasMany('App\Ward');
    }

     public function members()
    {
        return $this->hasMany('App\Member');
    }
}
