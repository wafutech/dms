<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReceiptNumber extends Model
{
    //
    protected $table = 'receipt_numbers';
    protected $fillable = ['number'];
}
