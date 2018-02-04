<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\View\Middleware\ErrorBinder;
use Session;
use App\County;
use App\Shareholdernextofkin;
use App\Member;
use App\Share;
use App\Report;
use DB;
use Carbon\Carbon;



class MemberReportsController extends Controller
{
	var $report_title = "Nzoia Grain Marketing and Processing Co-operative society";
    
  
   public function __construct()
   {
  $this->middleware('auth');

   }

   

    public function MembersListBySex($sex)
    {
    	//List members by sex: Male or Female
    	$memberBySex = DB::table('members')
    					->where('sex',$sex)
    					->orderBy('name','asc')
    					->get();
    	
    	 return view('reports.members.memberslistbysex',array('memberBySex'=>$memberBySex,
    	'report_title'=>$this->report_title));
    }

    public function MembersListByCounty($county)
    {
    	$countyName = DB::table('counties')
    					->where('count_name',$county)
    					->first();
    	//retrieve count_id
    		$MemberlistByCounty = DB::table('members')
    					->where('count_id',$countyName->id)
    					->orderBy('name','asc')
    					->groupBy('sub_county')
    					->get();
    					if($MemberlistByCounty==Null)
    					{
    						$message = "No member from"." ".$countyName->count_name." "."was found";
    				Session::flash('message', $message);
      				 return redirect()->back();
    					}
    					else
    					{

    	return view('reports.members.memberslistbycounty',array('MemberlistByCounty'=>$MemberlistByCounty,
    	'report_title'=>$this->report_title));
    }
    }
    public function ListMebersBySexAndSubcounty($sex,$subcounty)

    {
    	//the function requires 2 argument: sex and subcounty
    	$memberlist = DB::table('members')
    					->whereColumn(['sex','=',$sex],
    						['sub_county','like%',$subcounty])
    					->get();
    	if($memberlist==Null)
    					{
    						$message = "No member from the subcounty was found. Make sure the spelling are correct";
    				Session::flash('message', $message);
      				 return redirect()->back();
    					}
    					else
    					{

    	return view('reports.members.memberslistbysubcountysex',array('memberlist'=>$memberlist,
    	'report_title'=>$this->report_title));
    }

    }

    
   


    

    

    public function YouthMemberListBysex($sex)
    {
         $youthmembers = DB::table('members')
                    ->where(['age','<=',35],['sex','=',$sex])
                    ->get();
                    
 return view('reports.members.youthmembers',array('title'=>$this->report_title,
                    'youthmembers'=>$youthmembers));
        

    }

   

    public function shareMonthlyReport()
    {
        $now = Carbon::now();
        $startOfThisMonth = Carbon::instance($now)->startOfMonth();
        $currentOfThisMonth = Carbon::instance($now)->subSecond();

        $query = DB::table('shares')->whereBetween('date_paid',
                    [$startOfThisMonth, $currentOfThisMonth])->get();

      

       
        return view('reports.shares.sharemonthlyreport',array('title'=>$this->report_title,
                    'membershares'=>$query));


    }

     public function RegistrationFeeMonthlyReport()
    {
    	
    }


    public function MembersSharesSubtotal()

    {
        //This report lists members by their shares the way they 
        //paid in.
        
                /*$share_subtotals = DB::table('shares')
                ->select('amount',DB::raw('count(*) as total'))
                ->groupBy('member_number')
                ->orderBy('total','desc')
                ->get();*/

               /* $share_subtotals = DB::table('members')
                            ->leftjoin('shares','members.member_registration_number',
                             'shares.member_number','=','members.member_registration_number')
                            ->select('amount',DB::raw('sum() as total'))
                           // ->where('shares.amount','!=',Null)
                            ->orderBy('members.name','asc')
                            ->groupBy('amount')
                            ->sharedLock()
                            ->get();*/
                            //print_r($share_subtotals);exit;

                           /* $activeusers=\DB::table('todolists')
                            ->select('user_id',\DB::raw('count(*) as total'))
                            ->whereYear('create_at','=',2016)
                            ->whereMonth('updated_at','=',4)
                            ->groupBy('user_id')
                            ->orderBy('total','desc')
                            ->get();*/
            
       // return view('reports.shares.sharesubtotal',array('title'=>$this->report_title,
                  //  'membershares'=>$share_subtotals));
    }

    
}
