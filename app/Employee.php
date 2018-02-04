<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    //
    protected $guarded = ['id'];
    protected $table ='employees';
    protected $fillable = array('id','first_name','last_name','mobile_name',
    	'work_phone','phone_code','fax','email','postal_address',
    	'city','town','zip','phisical_address','profile','photo');

public function Share()
{
	return $this->hasMany('App\Share');
}
public function Member()
{
	return $this->hasMany('App\Member');
}
public function Order()
{
	return $this->hasMany('app\Order');
}

}
