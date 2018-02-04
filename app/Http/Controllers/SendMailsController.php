<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\testMail;
use Mail;
use App\Member;
use App\Certificate;

class SendMailsController extends Controller
{
    //

    public function testMail()
    {

    	$myEmail = 'okoaproject2007@gmail.com';
    	$member = Member::where('member_registration_number',6139)->first();
    	$regCert = Certificate::where('member_number',6139)->first();
    	Mail::to($myEmail)->send(new testMail($member,$regCert));

    	
    	dd("Mail Send Successfully");
    }
}
