<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Paymentmethod extends Model
{
    //
    protected $guarded = ['id'];
    protected $table ='payment_methods';
    protected $fillable = array('id','payment_method');
}
