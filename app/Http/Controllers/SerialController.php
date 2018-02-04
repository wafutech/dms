<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Session;
use App\County;
use App\Member;
use App\Shareholdernextofkin;
use App\Http\CustomClass\MembershipNumberGenerator;
use Carbon\Carbon;
use Validator;
use App\ReceiptNumber;
use DB;


class SerialController extends Controller
{
    //
      public function __construct() 
    {
$this->middleware(['auth']);
$this->middleware('role:admin');



     } 
    public function generateReceiptSerialsForm()
    {
    	return view('admin/receiptRequestForm',array('title'=>'Generate Receipts'));
    }
    public function receiptNumbers()
    {
    	$validation_rules = array(
          'startvalue'         => 'required|numeric',
          'endvalue'         => 'required|numeric',
                  
          
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
     if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      } 
    	$startValue = Input::get('startvalue');
    	$endValue = Input::get('endvalue');
    	/*if($startvalue>$endvalue)
    	{
    		echo "Start value must be less than end value";
    		exit;
    	}*/
    	//else
    	//{
    	for($i=$startValue;$i<=$endValue;$i++)
    	{
    		$rn = New ReceiptNumber;
    		$rn->number = $i;
        $rn->used=0;
    		$rn->save();

    	}
    	$message = "Receipt numbers from ".$startValue." to ".$endValue." have been generated and now ready for use";
    	 Session::flash('message', $message);
       return redirect()->back();
//}
}

public function verifyCertificateForm()
{
	return view('admin/verify',array('title'=>'Verify Certificate'));
}

public function verifyCertificate()
{
$id = Input::get('member_number');
$serial = Input::get('serial');

$verify = DB::table('certificates')

	->where('member_number','=',$id)
	->where('serial','=',$serial)
	->first();

	if($verify !=Null)
	{
		$verification_status = "valid";
        return Redirect::back()->with('message',$verification_status)->withInput();
	}
	else
	{
		$verification_status = "Fraud detecated! The certifcate with provided credentials does not exist!";
        return Redirect::back()->withErrors($verification_status);
	}
	
    // $verification_status;

}

//Generate password an option on user signup form
public function passwordGenerator()
{
  return str_random(8);
}


}
