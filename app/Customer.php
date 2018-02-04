<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //
    protected $guarded = ['id'];
    protected $table ='customers';
    protected $fillable = array('id','first_name','last_name','mobile_name',
    	'work_phone','phone_code','fax','email','postal_address',
    	'city','town','zip','phisical_address','custom_fields');
}
