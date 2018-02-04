<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Pagination\Paginator;
use App\Report;
use App\Member;
use App\Share;
use DB;
use Carbon\Carbon;
use Validator;
use PDF;
use Excel;
use App\County;
use App\Ward;
use App\Constituency;


class ReportsController extends Controller
{
    //
    var $reports;
    var $counties;
    	var $report_title = "Nzoia Grain Marketing and Processing Co-operative society";


     public function __construct() 
    {
     $this->middleware('auth');
    $this->reports =  Report::orderBy('report','asc')->pluck('report','report');
    $this->counties =  County::pluck('county_name','id');

    }

      public function Index()
   {
   	

    return view('reports.reportindex',array('title'=>'Reports','reports'=>$this->reports));
   }

   public function reportProcessor()
   {
    $report = Input::get('report');
    switch ($report) {
    	case 'Member Shares Today':
    		return $this->MemberTodaysShares();
    		break;
    		case 'Member Shares This Week':
    		return $this->MemberCurrentWeekShares();
    		break;
    		case 'This Quarter Member Shares':
    		return $this->MemberCurrentQuarterShares();
    		break;
    		case 'This Year Member Shares':
    		return $this->MemberCurrentYearShares();
    		break;    		
    		case 'Members Registered In This Period':
    		return $this->MemberRegistrationPeriodicReport();
    		break;
    		
    		case 'Shareholder Share Details':
    		return $this->MemberShares();
    		break;
    		case 'Shareholder Share Details Custom Report':
    		return $this->ShareholderCustomDetailReport();
    		break;
    		case 'Shareholder Summary':
    		return $this->ShareholderSummary();
    		break;
    		case 'Shareholder Summary Custom Report':
    		return $this->ShareholderSummaryCustomReport();
    		break;
    		case 'Youth Member List':
    		return $this->YouthMemberList();
    		break;
    		case 'Shares Received This Month':
    		return $this->ShareCurrentMonthReport();
    		break;
    		case 'Members Registered This Month':
    		return $this->currentMonthRegisteredMembers();
    		break;    	
    	default:
    	echo "We are waiting for your request";
    		# code...
    		break;
    }
    
   }

     public function MembersList()
    {
      //List all members with their basic information
      $memberlists= DB::table('members')
        ->leftjoin('counties','county_id',
          'counties.id','members.county_id')
        ->leftjoin('constituencies','constituency_id',
          'constituencies.id','members.constituency_id')
        ->leftjoin('wards','ward_id',
          'wards.id','members.ward_id')
       /* ->leftjoin('education_levels','education_level',
          'education_levels.id','members.education_level')
        ->leftjoin('certificates','member_registration_number',
          'certificates.member_number','members.member_registration_number')
        */
        ->get();

      $female_members = $memberlists->filter(
            function($female)
            {
              return $female->sex == "Female";
            });  
      $male_members = $memberlists->filter(
            function($male)
            {
              return $male->sex == "Male";
            }); 
            try
            {
             //Calculate % makeup of each sex
            $malePercentage = round((count($male_members)/count($memberlists))*100,2);
           $femalePercentage = round((count($female_members)/count($memberlists))*100,2); 
            }
            catch(\Exception $e)
            {
              return "Your request returned zero results, make sure there are registered members before trying the request again";
            }
            
            
      


       return view('reports.members.memberlist',array('memberlists'=>$memberlists,
      'title'=>'Members Register',
      'malePercentage'=>$malePercentage,'femalePercentage'=>$femalePercentage,
      'male_members'=>$male_members,'female_members'=>$female_members,
      'counties'=>$this->counties));
    }

    


   public function MembersContactlist()
    {
    $MembersContactlist = DB::table('members')
                        ->select(DB::raw('name'),DB::raw('sex'),DB::raw('phone_contact'),DB::raw('email'),
                          DB::raw('postal_address'),DB::raw('town'),
                          DB::raw('postal_code'),DB::raw('phisical_address'))
                          ->orderBy('name','asc')
                          ->get();

    
    return view('reports.members.membercontactlist',array('memberlists'=>$MembersContactlist,
    	'title'=>'Member Contacts'));

    }
    public function MemberTodaysShares()
    {
       $now = Carbon::now();
        $startOfThisDay = Carbon::instance($now)->startOfDay();
        $currentOfThisDay = Carbon::instance($now)->subSecond();

      $query = DB::table('members')
                ->leftjoin('shares','members.member_registration_number',
                 'shares.member_number','=','members.member_registration_number')
                           ->select(DB::raw('name'),DB::raw('member_number'),DB::raw('sum(amount) as total'))
                            ->whereBetween('shares.created_at',[$startOfThisDay,$currentOfThisDay])
                ->orderBy(DB::raw('name'),'asc')
                ->groupBy(DB::raw('member_number'),DB::raw('name'))
                ->sharedLock()
                ->get();

                            
                     $grandtotal = $query->sum('total');
                     $title = 'Today\'s Share Report';
       
        return view('reports.shares.shareDailyreport',array('title'=>$title,
                    'membershares'=>$query,'grandtotal'=>$grandtotal));

    }

    public function MemberCurrentWeekShares()
    {
       $now = Carbon::now();
        $startOfThisWeek = Carbon::instance($now)->startOfWeek();
        $currentOfThisWeek = Carbon::instance($now)->subSecond();

      $query = DB::table('members')
                ->leftjoin('shares','members.member_registration_number',
                 'shares.member_number','=','members.member_registration_number')
                           ->select(DB::raw('name'),DB::raw('member_number'),DB::raw('sum(amount) as total'))
                            ->whereBetween('shares.created_at',[$startOfThisWeek,$currentOfThisWeek])
                ->orderBy(DB::raw('name'),'asc')
                ->groupBy(DB::raw('member_number'),DB::raw('name'))
                ->sharedLock()
                ->get();

                            
                     $grandtotal = $query->sum('total');
       
        return view('reports.shares.shareWeeklyreport',array('title'=>'This Week Share Report',
                    'membershares'=>$query,'grandtotal'=>$grandtotal));
      
    }

     public function ShareCurrentMonthReport()
    {
        $now = Carbon::now();
        $startOfThisMonth = Carbon::instance($now)->startOfMonth();
        $currentOfThisMonth = Carbon::instance($now)->subSecond();
        $month =explode('-', $currentOfThisMonth);
        switch ($month[1]) {
          case '01':
         $monthString = 'January';
            break;
            case '02':
          $monthString = 'February';
              break;
              case '03':
          $monthString = 'March';
                break;
             case '04':
              $monthString = 'April';
                  break;
            case '05':
             $monthString = 'May';
              break;
              case '06':
               $monthString = 'June';
                break;
                case '07':
                 $monthString = 'July';
                  break;
                  case '08':
                 $monthString = 'August';
                    break;
                case '09':
                $monthString = 'September';

                  break;
                  case '10':
                 $monthString = 'October';

                    break;
                    case '11':
                   $monthString = 'November';

                      break;
                      case '12':
                    $monthString = 'December';

                        break;

          
          default:
            echo "No request received";
            break;
        }

      
        		$query = DB::table('members')
        			->leftjoin('shares','member_registration_number',
        				'shares.member_number','=','members.member_registration_number')
        			->select(DB::raw('members.name'),DB::raw('shares.member_number'),DB::raw('sum(amount)as total'))
        			->where('shares.amount','!=',Null)
        			->whereBetween('date_paid',[$startOfThisMonth,$currentOfThisMonth])
        			->groupBy(DB::raw('member_number'),DB::raw('name'))
    				->sharedLock()
        			->get();

                            
                     $grandtotal = $query->sum('total');
                     $title = 'Share Report since the beginning of'.' '.$monthString.' '.$month[0];
       
        return view('reports.shares.sharemonthlyreport',array('title'=>$title,
                    'membershares'=>$query,'grandtotal'=>$grandtotal));


    }

    public function MemberCurrentQuarterShares()
    {
      $now = Carbon::now();
        $startOfThisQuarter = Carbon::instance($now)->startOfQuarter();
        $currentOfThisQuarter = Carbon::instance($now)->subSecond();

        /*$query = DB::table('shares')->whereBetween('date_paid',
                    [$startOfThisMonth, $currentOfThisMonth])*/
            $query = DB::table('members')
              ->leftjoin('shares','member_registration_number',
                'shares.member_number','=','members.member_registration_number')
              ->select(DB::raw('members.name'),DB::raw('shares.member_number'),DB::raw('sum(amount)as total'))
              ->where('shares.amount','!=',Null)
              ->whereBetween('date_paid',[$startOfThisQuarter,$currentOfThisQuarter])
              ->groupBy(DB::raw('member_number'),DB::raw('name'))
            ->sharedLock()
              ->get();

                            
                     $grandtotal = $query->sum('total');
       
        return view('reports.shares.shareQuarterlyReport',array('title'=>'This Quarter Share Report by Member',
                    'membershares'=>$query,'grandtotal'=>$grandtotal));


    }


     public function MemberCurrentYearShares()
    {
      $now = Carbon::now();
        $startOfThisYear = Carbon::instance($now)->startOfYear();
        $currentOfThisYear = Carbon::instance($now)->subSecond();

        /*$query = DB::table('shares')->whereBetween('date_paid',
                    [$startOfThisMonth, $currentOfThisMonth])*/
            $query = DB::table('members')
              ->leftjoin('shares','member_registration_number',
                'shares.member_number','=','members.member_registration_number')
              ->select(DB::raw('members.name'),DB::raw('shares.member_number'),DB::raw('sum(amount)as total'))
              ->where('shares.amount','!=',Null)
              ->whereBetween('date_paid',[$startOfThisYear,$currentOfThisYear])
              ->groupBy(DB::raw('member_number'),DB::raw('name'))
            ->sharedLock()
              ->get();

                            
                     $grandtotal = $query->sum('total');
       
        return view('reports.shares.shareAnualReport',array('title'=>'This Year Share Report by Member',
                    'membershares'=>$query,'grandtotal'=>$grandtotal));
     
    }

    public function currentMonthRegisteredMembers()
    {
    	$now = Carbon::now();
        $startOfThisMonth = Carbon::instance($now)->startOfMonth();
        $currentOfThisMonth = Carbon::instance($now)->subSecond();

        $query = DB::table('members')->whereBetween('created_at',
                    [$startOfThisMonth, $currentOfThisMonth])->get();


        return view('reports.members.memberlist',array('memberlists'=>$query,
    	'title'=>$this->report_title));
    }

   

    public function MemberRegistrationPeriodicReport()
    {
    	 $validation_rules = array(
          'start_date'         => 'required|date|before:tomorrow',
          'end_date'         => 'required|date|before:tomorrow',
                    
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
     if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      } 

    	$start_date = Input::get('start_date');
    	$end_date  = Input::get('end_date');

    	$query = DB::table('members')->whereBetween('registration_date',
                    [$start_date, $end_date])->get();

    	 return view('reports.members.memberlist',array('memberlists'=>$query,
    	'title'=>$this->report_title));
    }

    public function ShareholderSummary()

    {
    	//This report lists members by their shares the way they 
    	//paid in.
    	$member_shares = DB::table('members')
    						->leftjoin('shares','members.member_registration_number',
    						 'shares.member_number','=','members.member_registration_number')
                           ->select(DB::raw('name'),
                            DB::raw('idnumber'),
                            DB::raw('member_number'),DB::raw('sum(amount) as total'))
                            ->where('shares.amount','!=',Null)
    						->orderBy(DB::raw('name'),'asc')
    						->groupBy(DB::raw('member_number'),DB::raw('name'),DB::raw('idnumber'))
    						->sharedLock()
    						->get();
                           
                         $grandtotal = $member_shares->sum('total');

                            
    	return view('reports.shares.membershares',array('title'=>'Member Share Summary',
    				'membershares'=>$member_shares,'grandtotal'=>$grandtotal));
    }

    public function ShareholderCustomDetailReportForm()
    {
      return view('reports.shares.sharePeriodicReportsForm',['title'=>'Custom Periodic Report']);
    }
    public function ShareholderCustomDetailReport()

    {

    	//This report lists members by their shares the way they 
    	//paid in.
    	$validation_rules = array(
          'start_date'         => 'required|date|before:tomorrow',
          'end_date'         => 'required|date|before:tomorrow',
                    
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
     if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      } 

    	$start_date = Carbon::parse(Input::get('start_date'));
    	$end_date  = Carbon::parse(Input::get('end_date'));

    	$query = DB::table('shares')->whereBetween('created_at',
                    [$start_date, $end_date])->get();

$query = DB::table('members')
        			->leftjoin('shares','member_registration_number',
        				'shares.member_number','=','members.member_registration_number')
        			->select(DB::raw('members.name'),DB::raw('shares.member_number'),DB::raw('sum(amount)as total'))
        			->where('shares.amount','!=',Null)
        			->whereBetween('date_paid',[$start_date,$end_date])
        			->groupBy(DB::raw('member_number'),DB::raw('name'))
    				->sharedLock()
        			->get();

                            
                     $grandtotal = $query->sum('total');
       
        return view('reports.shares.sharecustomreports',array('title'=>'Share by Member Periodic Report',
                    'membershares'=>$query,'grandtotal'=>$grandtotal,'start_date'=>$start_date,
                    'end_date'=>$end_date));

 	                    

    }
public function MemberByLandAcrage()
{

	$land_acres = DB::table('members')
				->select(DB::raw('name'),DB::raw('member_registration_number'),DB::raw('landsize'),
					DB::raw('sum(landsize) as total'))
				->groupBy(DB::raw('member_registration_number'),DB::raw('name'),DB::raw('landsize'))
				->orderBy(DB::raw('name','asc'))
				//->sharelock()
				->get();	
				

				$grandtotal = $land_acres->sum('total');
				return view('reports.land.memberland',array('title'=>'Members with Land',
                    'memberlands'=>$land_acres,'grandtotal'=>$grandtotal,'counties'=>$this->counties));
}
public function YouthMemberList()
    {
        $members = Member::all();
        $youthmembers = $members->filter(
          function($member)
          {
            return $member->year_of_birth->diffInYears(Carbon::now())<=35;
          });
        //Filter members by sex
          $male_members = $youthmembers->filter(
            function($male)
            {
              return $male->sex == "Male";
            });  

            $female_members = $youthmembers->filter(
            function($female)
            {
              return $female->sex == "Female";
            });  

            //Calculate % makeup of each sex
            $malePercentage = round((count($male_members)/count($youthmembers))*100,2);
           $femalePercentage = round((count($female_members)/count($youthmembers))*100,2);               

 return view('reports.members.youthmembers',array('title'=>$this->report_title,
                    'youthmembers'=>$youthmembers,'counties'=>$this->counties,
                    'malePercentage'=>$malePercentage,
                    'femalePercentage'=>$femalePercentage,
                    'male_members'=>$male_members,'female_members'=>$female_members));
}



public function MemberShareStatementForm()
{
	return view('reports.members.memberstatementform',array('title'=>$this->report_title,
                    ));

}
public function MemberShareStatement()
{
	$validation_rules = array(
          
          'start_date'         => 'required|date|before:tomorrow',
          'end_date'         => 'required|date|before:tomorrow',
          'member_number' =>'required|exists:members,member_registration_number'
                    
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
     if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      } 

    	$number = Input::get('member_number');
    	$start_date = Input::get('start_date');
    	$end_date  = Input::get('end_date');
    	$member = Member::where('member_registration_number',$number)->first();

    	$query = DB::table('members')
        			->leftjoin('shares','member_registration_number',
        				'shares.member_number','=','members.member_registration_number')
        			->select(DB::raw('members.name'),DB::raw('shares.member_number')
        				,DB::raw('members.created_at'),DB::raw('shares.receipt_no'),
        				DB::raw('shares.date_paid'),DB::raw('shares.amount'))
        			->where('members.member_registration_number','=',$number)
        			->where('shares.member_number','=',$number)
        			->where('shares.amount','!=',Null)
        			->whereBetween('shares.date_paid',[$start_date,$end_date])
        			->orderBy(DB::raw('shares.date_paid','desc'))
    				->sharedLock()
        			->get();

        				//calculate subtotals
				$subtotal = [];
               
				foreach($query as $q)
				{
					 array_push($subtotal, $q->amount);
					 //$timeInterval = $end_date->diffInMonths($start_date);


				}
				$subtotal = array_sum($subtotal);
				//echo $timeInterval;exit;
				
        			


   	return view('reports.members.memberstatement',array('title'=>$this->report_title,
     'membershares'=>$query,'accountnumber'=>$number,'start_date'=>$start_date,
          'end_date'=>$end_date,'member'=>$member,'total'=>$subtotal));


}
public function MembersListBySexForm()
{
return view('reports.members.memberlistbysexForm');
}

//List members by requested sex
public function MembersListBySex()
{
  //validate input
  $validation_rules = array(
          
          'sex'         => 'required',
                    
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
     if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      } 

  //get input
  $sex = Input::get('sex');
  $memberlists = DB::table('members')
         ->leftjoin('counties','county_id',
          'counties.id','members.county_id')
        ->leftjoin('constituencies','constituency_id',
          'constituencies.id','members.constituency_id')
        ->leftjoin('wards','ward_id',
          'wards.id','members.ward_id')        
      ->where('sex',$sex)->get();

  $title = $sex." "."Members";
  $count = count($memberlists);

  if($count ==Null)
  {
    $message = "No results for ".$sex." were found";
  return  Redirect::back()->with('message',$message)->withInput();

  }
  else
  {

  
  return view('reports.members.memberlistBySex',array('memberlists'=>$memberlists,
     'title'=>$title,'total'=>$count,'sex'=>$sex));

   


}
}

public function memberLandByCounty(Request $request)
    {

    $county = $request->input('county');
       $land_acres = DB::table('members')
        ->select(DB::raw('name'),DB::raw('member_registration_number'),DB::raw('landsize'),
          DB::raw('sum(landsize) as total'))
        ->where('county_id',$county)
        ->groupBy(DB::raw('member_registration_number'),DB::raw('name'),DB::raw('landsize'))
        ->orderBy(DB::raw('name','asc'))
        //->sharelock()
        ->get(); 
        
        $grandtotal = $land_acres->sum('total');
        return view('reports.land.memberland',array('title'=>'Members land by County',
                    'memberlands'=>$land_acres,'grandtotal'=>$grandtotal,'counties'=>$this->counties));
      
    }

    public function MemberLandAndShares()
    {
      $member_shares = DB::table('members')
                ->leftjoin('shares','members.member_registration_number',
                 'shares.member_number','=','members.member_registration_number')
                ->leftjoin('counties','members.county_id','counties.id','members.county_id')
                           ->select(DB::raw('name'),DB::raw('landsize'),DB::raw('member_registration_number'),
                            DB::raw('county_name'),DB::raw('sum(amount) as total'))
                           // ->where('shares.amount','!=',Null)
                ->orderBy(DB::raw('county_name'),'asc')
                ->groupBy(DB::raw('member_registration_number'),DB::raw('county_name'),DB::raw('name'),DB::raw('landsize'))
                ->sharedLock()
                ->get();
                $total_land =$member_shares->sum('landsize');          
             $grandtotal = $member_shares->sum('total');
           return view('reports.shares.membersharewithland',array('title'=>'Members Share with Land',
                    'members'=>$member_shares,'grandtotal'=>$grandtotal,'counties'=>$this->counties,'total_land'=>$total_land));  
    }

    public function MemberSharesDetailReport()
    {
      $members = Member::with('Share')->get();
      
      return view('reports.shares.memberWithshares',
        array('title'=>'Share Details Report',
          'members'=>$members));

    }

    public function MembersWithSkills()
    {
      $members = Member::with('skills')
      ->orderBy('name','asc')
      ->get();
      
      return view('reports.members.membwerwithskills',
        array('members'=>$members,'title'=>'Members  with Skills' ));
    }

    public function MemberListByConstituencies()
    {
            $members = Constituency::with('members')
           
            ->orderBy('const_name','asc')
            ->get();
            return view('reports.members.membwerwithConstituencies',
        array('members'=>$members,'title'=>'Members in each Constituency' ));

    }

    public function MemberListByWards()
    {
            $members = Ward::with('members')
            ->orderBy('ward','asc')
            ->get();
           // print_r($members);exit;
            return view('reports.members.membwerwithWards',
        array('members'=>$members,'title'=>'Members in each Ward',
        'counties'=>$this->counties ));

    }


}
