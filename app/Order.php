<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $guarded = ['id'];
    protected $table ='orders';
    protected $fillable = array('id','customers_id','order_date','order_status',
    	'ship_date','ship_name','ship_address','ship_city','ship_zip',
    	'ship_fee','payment_method_id','paid_date','notes','employee_id');

    public function Customer()
    {
    	return $this->belengsTo('App\Customer','id');
    }
     public function Employee()
    {
    	return $this->belengsTo('App\Employee','id');
    }
     public function Paymentmethod()
    {
    	return $this->belengsTo('App\Paymentmethod','id');
    }
}
