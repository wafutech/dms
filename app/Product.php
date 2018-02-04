<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $guarded = ['id'];
    protected $table ='products';
    protected $fillable = array('id','product_name','product_description','category_id',
    	'product_code','min_order','supply_capacity','quantity','product_image',
    	'price','model','make');

    public function ProductCategory()
    {
    	return $this->belongTo('App\ProductCategory','id');
    }
    public function  Order()
    {
    	return $this->hasMany('App\Order','id');
    }
}
