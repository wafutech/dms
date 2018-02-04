<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReceiptDownload extends Model
{
    //
        protected $table = 'receipt_downloads';
        protected $fillable = ['member_number','download_path','refno'];

}
