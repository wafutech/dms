<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Pagination\Paginator;
use App\Member;
use Session;
use Auth;
use DB;

class MemberCredentialVerificationController extends Controller
{
    public function __construct() 
    {
$this->middleware(['auth']);
$this->middleware('role:admin');




     }  

    public function index()
    {
    //Dashboard
          $incomplete_members = DB::table('members')->where('idnumber',null)->orWhere('phone_contact',null)->orWhere('year_of_birth',null)->orWhere('name',null)->get(); 

          $withoutids = Member::where('idnumber',null)->get();
          $withoutphones = Member::where('phone_contact',null)->get();
          $withoutdob = Member::where('year_of_birth',null)->get();
          $withoutnames = Member::where('name',null)->get();
          $withoutland = Member::where('landsize',null)->get();
          $withoutshares = Member::where('idnumber',null)->get();
          $withoutemails = Member::where('email',null)->get();
          $withoutshares = DB::table('members')->join('shares','member_registration_number','shares.member_number','members.member_registration_number')->get();
          $totalMembers = Member::all();

          




          return view('members/verification/index',array('title'=>'Records that require some fixing','withoutids'=>$withoutids,'withoutphones'=>$withoutphones,'withoutdob'=>$withoutdob,'withoutnames'=>$withoutnames,'withoutland'=>$withoutland,'withoutshares'=>$withoutshares,'withoutemails'=>$withoutemails,'totalMembers'=>$totalMembers));

      }

      public function verificationDetails($id)
      {
      	$members = '';
      	$title = '';
      	switch ($id) {
      		case 'idnumber':
      		$withoutids = Member::where('idnumber',null)->get();
      		$members = $withoutids;
      	    $title = 'Members Without National ID Numbers';


      			break;
      			case 'name':
          $withoutnames = Member::where('name',null)->get();
          $members =$withoutnames;
          $title = 'Members Without Names';

      			break;
      			case 'dob':
      	$withoutdob = Member::where('year_of_birth',null)->get();
      		$members =$withoutdob;
      		 $title = 'Members Without Date of Births';

      			break;
      			case 'phone':
      	$withoutphones = Member::where('phone_contact',null)->get();
      	$members = $withoutphones;
      	$title = 'Members Without Phone Numbers';


      			break;
      			case 'land':
       $withoutland = Member::where('landsize',null)->get();
       $members =$withoutland;
       $title = 'Members Without Land Size';


      			break;
      			case 'email':
        $withoutemails = Member::where('email',null)->get();
        $members =$withoutemails;
         $title = 'Members Without Emails';


      			break;
      			case 'shares':
       $withoutshares = Member::where('shares',null)->get();
       $members =$withoutshares;
       $title = 'Members Without Shares';

      			break;
      		
      		default:
      			return Redirect::back();
      			break;
      	}
      	return view('members.verification.details',['title'=>$title,'members'=>$members]);
      }


}
