<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
//use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\View\Middleware\ErrorBinder;
use Illuminate\Pagination\Paginator;
use Session;
use App\Share;
use App\Member;
use Validator;
use DB;
use Carbon\Carbon;
use PhpOffice\PhpWord\Settings;
use App\ReceiptNumber;
use App\ReceiptDownload;
use PDF;
use Auth;
use App\Certificate;
use Storage;
use File;


class ShareCertificateController extends Controller
{
    //
    public function certificateRequestForm()
    {
    	return view('admin.sharecertificateForm',array('title'=>"Shareholder Certificate Request Form"));
    }

    public function ShareCertProcessor(Request $request)
    {
    	$validation_rules = array(
          'member_number'         => 'bail|required|numeric|exists:members,member_registration_number',
          'min_share'         => 'required|numeric',
         
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
      if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      } 
    	$shareholder = Input::get('member_number');
    	$lower_limit = Input::get('min_share');
    	//Check if the member have passed the minimum requirement for certification
    	$check_validity = Share::where('member_number',$shareholder)->get();
    	
    	if($check_validity==null)
    	{
    		return Redirect::back()->withErrors('No shares were found for this member');
    	}
    	//If some shares were found, check if the shareholder meets the minimum share amount to get a share certificate
    	$share_amount = 0;
    	foreach ($check_validity as  $value)
    	 {
    	$share_amount+=$value->amount;
    	}
    	if($share_amount<$lower_limit)
    	{
    		$error = "Sorry, you are yet to meet the lowest share amount to get a share certificate. The current share amount is Kes.".number_format($share_amount,2)." .The minimum share amount requred is kes.".number_format($lower_limit)." . Please pay in ".number_format($lower_limit-$share_amount,2)." more in order to qualify for a share holder certificate";
    		return Redirect::back()->withErrors($error);
    	}
    	//Else process the certicate
    	$member = Member::where('member_registration_number',$shareholder)->first();

    	$fc = $member->name[0];
      $sortcode = substr($member->member_registration_number, -2)*2;
      $r = rand(1,10);

$sn = $fc.$sortcode.$r.$member->idnumber.$member->member_number;
        $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');
$pdf = PDF::loadView('docs.shareholdercert', array('name'=>$member->name,
      'MembershipNumber'=>$member->member_registration_number,'idnumber'=>$member->idnumber,
      'serial'=>$sn,'regdate'=>date('d-m-Y')))->setOption('footer-left','')->setOption('footer-center','')->setOption('header-center','');

/* Save certificate of registration -File path*/
$filename = $member->name."-".$member->member_registration_number;
    	

$path = 'docs/shareCerts/'.$filename.'.pdf';
//echo $path;exit;



     if (File::exists('docs/shareCerts/'.$filename.'.pdf')) 
 {
  try
  {
    unlink('docs/shareCerts/'.$filename.'.pdf');
          //Delete the link from database storage
     DB::table('certificates')->where('download_path', '=', $path)->delete();
          //$file_path  = Certificate::where('download_path',$path.'-'.$shareholder.'.pdf')->first();
        

  }
  catch(Exception $e)
  {
$e->getMessage();
  }
          




   }

   
 //Save the new certificate
 $pdf->save('docs/shareCerts/'.$filename.'.pdf');


//save certificate details in the database
$cert = New Certificate;
$cert->member_number = $member->member_registration_number;
$cert->download_path = $path;
$cert->serial =$sn;
$cert->save();
$message = "Your request was successful. Click the link below to view and print the certificate.";
return view('admin.download',array('title'=>'Request successfull','message'=>$message,'path'=>$path));

return Redirect::back()->with('message',$message);

    

  }
    public function batchProcessShareCerticatesForm()
    {
    	return view('admin.batchsharecertificateForm',array('title'=>'Shareholders With Certificates Request'));
    }

  public function batchProcessShareCerticates()
    {
    	$validation_rules = array(
          'min_share'         => 'required|numeric',
         
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
      if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      } 
    $lower_limit = Input::get('min_share');
    $shares = DB::table('shares')
    		->join('members','shares.member_number','members.member_registration_number')
    		->select(DB::raw('sum(amount) as total'),(DB::raw('member_number')),
    			(DB::raw('name')),
    			(DB::raw('idnumber')))
    		->groupBy('name','idnumber','member_number')
    		->orderBy('total','asc')
    		->get();
        $countCerts =0;
    foreach($shares as $share)
    {
    	if($share->total>=$lower_limit)
    	{
        $countCerts+=$countCerts+1;
    		//Process certicates

    		$fc = $share->name[0];
      $sortcode = substr($share->member_number, -2)*2;

$sn = $fc.$sortcode.$share->idnumber.$share->member_number;
        $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');
$pdf = PDF::loadView('docs.shareholdercert', array('name'=>$share->name,
      'MembershipNumber'=>$share->member_number,'idnumber'=>$share->idnumber,
      'serial'=>$sn,'regdate'=>date('d-m-Y')));



/* Save certificate of registration -File path*/
$filename = $share->name."-".$share->member_number;
    	

$path = 'docs/shareCerts/'.$filename.'.pdf';
if (File::exists('docs/shareCerts/'.$filename.'.pdf')) 
 {
  try
  {
    unlink('docs/shareCerts/'.$filename.'.pdf');
          //Delete the link from database storage
     DB::table('certificates')->where('download_path', '=', $path)
     ->orWhere('serial',$sn)->delete();

          //$file_path  = Certificate::where('download_path',$path.'-'.$shareholder.'.pdf')->first();
        

  }
  catch(Exception $e)
  {
$e->getMessage();
  }
          




   }

 $pdf->save('docs/shareCerts/'.$filename.'.pdf');
    	
    


    //save certificate details in the database
$cert = New Certificate;
$cert->member_number = $share->member_number;
$cert->download_path = $path;
$cert->serial =$sn;
$cert->save();
$message = "Your request was successfull. Use the links below to view and print each certificate";
//return Redirect::back()->with('message',$message);
//return view('admin.download',array('title'=>'Request successfull','message'=>$message,'path'=>$path));
}
}
$certificates = DB::table('certificates')
				->join('members','certificates.member_number','members.member_registration_number','certificates.member_number')
				->join('shares','certificates.member_number','shares.member_number','certificates.member_number')
				->select(
					DB::raw('sum(amount) as total'),
				(DB::raw('certificates.member_number')),
				(DB::raw('members.name')),
				(DB::raw('certificates.download_path')))
				->groupBy('member_number','name','download_path')
				->orderBy('total','desc')
				->get();

  return view('admin.shareholders_with_certs',['title'=>'Shareholders with Certificates','message'=>$message,'certificates'=>$certificates,'lower_limit'=>$lower_limit,'countCerts'=>$countCerts]);



    }
}
