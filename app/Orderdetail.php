<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Orderdetail extends Model
{
    //
    protected $guarded = ['id'];
    protected $table ='orders_details';
    protected $fillable = array('id','order_id','product_id','quantity',
    	'units','unit_price','amount','order_status_id');
    public function  Order()
    {
    	return $this->belongsTo('App\Order','id');
    }
    public function  Product()
    {
    	return $this->belongsTo('App\Product','id');
    }

    public function  Orderstatus()
    {
    	return $this->belongsTo('App\Orderstatud','id');
    }
     public function  Order()
    {
    	return $this->hasMany('App\Order','id');
    }
}
