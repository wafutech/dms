<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Session;
use App\Share;
use App\Member;
use Validator;
use DB;
use Carbon\Carbon;
use PhpOffice\PhpWord\Settings;
use App\ReceiptNumber;
use App\ReceiptDownload;
use App\Skill;

class MemberProfileController extends Controller
{
    //
      public function __construct() 
    {
$this->middleware(['auth']);

     } 
    public function profileLoginForm()
    	{

    		return view('members.create_profile',array('title'=>'Member Profile Login!'));
    	}

    	public function index()
    	{
    		$validation_rules = array(
          'id'           => 'required|numeric|digits_between:4,6|exists:members,member_registration_number',

          
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
     if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      } 
    		$id =Input::get('id');
    		$profile = DB::table('members')
    		->leftjoin('counties','county_id',
    			'counties.id','members.county_id')
            ->leftjoin('constituencies','constituency_id',
                'constituencies.id','members.constituency_id')
            ->leftjoin('wards','ward_id',
                'wards.id','members.ward_id')
    		->leftjoin('education_levels','education_level',
    			'education_levels.id','members.education_level')
    		/*->leftjoin('users','user_id',
    			'users.id','members.user_id')*/
    		->leftjoin('certificates','member_registration_number',
    			'certificates.member_number','members.member_registration_number')

    		->where('member_registration_number',$id)
    		->first();
    		//fetch skills
    		$skills = SKill::where('member_number',$id)->get();
    		//$skills =  json_encode($skills);


            //fetch shares
    		$shares = DB::table('shares')    			
    			   ->select(DB::raw('member_number'),DB::raw('date_paid'),DB::raw('amount'),DB::raw('sum(amount) as total'))
    			   ->where('member_number','=',$id)
    			 ->where('shares.amount','!=',Null)
    			 ->groupBy('member_number','date_paid','amount')
    			 ->get();
    			 
    			 $total_share=$shares->sum('total');
    		$receipts = DB::table('receipt_downloads')
    					->where('member_number',$id)
    					->orderBy('created_at','desc')
    					->get();
            $memberName = Member::where('member_registration_number',$id)->first();

    		return view('members.member_profile',array('title'=>'Member Profile',
                    'profile'=>$profile,'skills'=>$skills,
                    'shares'=>$shares,'total_share'=>$total_share,
                    'receipts'=>$receipts,'name'=>$memberName));
    		
    	}

        public function MemberProfileByAdmin($id)
        {
            $profile = DB::table('members')
            ->leftjoin('counties','county_id',
                'counties.id','members.county_id')
            ->leftjoin('constituencies','constituency_id',
                'constituencies.id','members.constituency_id')
            ->leftjoin('wards','ward_id',
                'wards.id','members.ward_id')
            ->leftjoin('education_levels','education_level',
                'education_levels.id','members.education_level')
            ->leftjoin('users','user_id',
                'users.id','members.user_id')
            ->leftjoin('certificates','member_registration_number',
                'certificates.member_number','members.member_registration_number')

            ->where('member_registration_number',$id)
            ->first();
            //fetch skills
           $skills = SKill::where('member_number',$id)->get();
           $skills = json_decode($skills);
            //fetch shares
            $shares = DB::table('shares')               
                   ->select(DB::raw('member_number'),DB::raw('date_paid'),DB::raw('amount'),DB::raw('sum(amount) as total'))
                   ->where('member_number','=',$id)
                 ->where('shares.amount','!=',Null)
                 ->groupBy('member_number','date_paid','amount')
                 ->get();
                 
                 $total_share=$shares->sum('total');
            $receipts = DB::table('receipt_downloads')
                        ->where('member_number',$id)
                        ->orderBy('created_at','desc')
                        ->get();
                 $memberName = Member::where('member_registration_number',$id)->first();


            return view('members.member_profile',array('title'=>'Member Profile',
                    'profile'=>$profile,'skills'=>$skills,
                    'shares'=>$shares,'total_share'=>$total_share,
                    'receipts'=>$receipts,'name'=>$memberName));

        }

}
