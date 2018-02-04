<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shareholdernextofkin extends Model
{
    //
        use SoftDeletes;

    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];
    protected $table ='shareholder_nextofkins';
    protected $fillable = array('id','member_number','first_name',
    	'last_name','id_number','mobile_phone','email','postal_address',
    	'town','zip','county_id','subcounty','constituency_id','ward_id','phisical_address',
    	'relationship','employee_id');
    public function Member()
    {
    	return $this->belongsTo('App\Member','member_registration_number');
    }
    public function County()
    {
    	return $this->belongsTo('App\County','id');
    }
}
