<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Share;
use App\Member;
use Validator;
use DB;
use Carbon\Carbon;
use App\County;
use App\Constituency;
use Illuminate\Support\Facades\Response;
use App\Ward;
use App\Report;

class ReportFiltersController extends Controller
{
    //
  
    var $counties;
    public function __construct()
    {
    $this->middleware('auth');
    $this->counties =  County::pluck('county_name','id');
    $this->reports =  Report::orderBy('report','asc')->pluck('report','report');

    }

    public function index()
    {
      return view('reports.filters.index',[
        'title'=>'Report Filters',
        'counties'=>$this->counties,'reports'=>$this->reports]);
    }
    public function memberByCounty(Request $request)
    {
    	$county = $request->input('county');
    	//echo $county;exit;
    	 //List all members with their basic information

       $memberlist= DB::table('members')
        ->leftjoin('counties','county_id',
          'counties.id','members.county_id')
        ->leftjoin('constituencies','members.constituency_id',
          'constituencies.id','members.constituency_id')
        ->leftjoin('wards','ward_id',
          'wards.id','members.ward_id')
        ->leftjoin('education_levels','education_level',
          'education_levels.id','members.education_level')
        ->leftjoin('certificates','member_registration_number',
          'certificates.member_number','members.member_registration_number')
        ->leftjoin('users','user_id',
          'users.id','members.user_id')
        ->where('members.county_id',$county)
        ->paginate(20);
        //->get();

      $female_members = $memberlist->filter(
            function($female)
            {
              return $female->sex == "Female";
            });  
      $male_members = $memberlist->filter(
            function($male)
            {
              return $male->sex == "Male";
            }); 

            //Calculate % makeup of each sex
      $result = count($memberlist);
      if($result=0)
      {
          return redirect()->back()->with('message','No results was found for the search query') ; 
     
      }
      else
      {
        try
        {
$malePercentage = round((count($male_members)/count($memberlist))*100,2);
$femalePercentage = round((count($female_members)/count($memberlist))*100,2);
}
catch(\Exception $e)
{
 return "SORRY! We could not find any member associated with the county selected"; 
}
foreach($memberlist as $county)
{
  $county = $county->county_name;
}
$title = "List of Members in ".$county." County";

       return view('reports.members.memberlist',array('memberlists'=>$memberlist,
      'title'=>$title,
      'malePercentage'=>$malePercentage,'femalePercentage'=>$femalePercentage,
      'male_members'=>$male_members,'female_members'=>$female_members,'counties'=>$this->counties));
    	}

    }

    public function memberBySubCounty(Request $request)
    {

    $subcounty = $request->input('subcounty');
    	 //List all members with their basic information
      $memberlist= DB::table('members')
        ->leftjoin('counties','county_id',
          'counties.id','members.county_id')
        ->leftjoin('education_levels','education_level',
          'education_levels.id','members.education_level')
        ->leftjoin('certificates','member_registration_number',
          'certificates.member_number','members.member_registration_number')
        ->leftjoin('users','user_id',
          'users.id','members.user_id')
        ->where('sub_county',$subcounty)
        ->get();

      $female_members = $memberlist->filter(
            function($female)
            {
              return $female->sex == "Female";
            });  
      $male_members = $memberlist->filter(
            function($male)
            {
              return $male->sex == "Male";
            }); 

            //Calculate % makeup of each sex
      $result = count($memberlist);
      if($result=0)
      {
          return redirect()->back()->with('message','No results was found for the search query') ; 
     
      }
      else
      {
$malePercentage = round((count($male_members)/count($memberlist))*100,2);
$femalePercentage = round((count($female_members)/count($memberlist))*100,2);
$title = "List of Members in ".$subcounty." subcounty.";

       return view('reports.members.memberlist',array('memberlists'=>$memberlist,
      'title'=>$title,
      'malePercentage'=>$malePercentage,'femalePercentage'=>$femalePercentage,
      'male_members'=>$male_members,'female_members'=>$female_members,
      'counties'=>$this->counties));
    	}
    	
    }

    public function memberByWard(Request $request)

    {
    	$ward = $request->input('ward');
    	 //List all members with their basic information
      $memberlist= DB::table('members')
        ->leftjoin('counties','county_id',
          'counties.id','members.county_id')
        ->leftjoin('education_levels','education_level',
          'education_levels.id','members.education_level')
         ->leftjoin('certificates','member_registration_number',
          'certificates.member_number','members.member_registration_number')
        ->where('ward','LIKE','%'.$ward.'%')
          ->leftjoin('users','user_id',
          'users.id','members.user_id')
        ->get();

      $female_members = $memberlist->filter(
            function($female)
            {
              return $female->sex == "Female";
            });  
      $male_members = $memberlist->filter(
            function($male)
            {
              return $male->sex == "Male";
            }); 

            //Calculate % makeup of each sex
      $result = count($memberlist);
      if($result=0)
      {
          return redirect()->back()->with('message','No results was found for the search query') ; 
     
      }
      else
      {
$malePercentage = round((count($male_members)/count($memberlist))*100,2);
$femalePercentage = round((count($female_members)/count($memberlist))*100,2);
$title = "List of Members in ".$ward." ward.";

       return view('reports.members.memberlist',array('memberlists'=>$memberlist,
      'title'=>$title,
      'malePercentage'=>$malePercentage,'femalePercentage'=>$femalePercentage,
      'male_members'=>$male_members,'female_members'=>$female_members,
      'counties'=>$this->counties));
    	}
    }

    public function shareReportByLocation()
    {
      $validation_rules = array(
          'location'         => 'required',             
          
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
      if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      } 

      $location = Input::get('location');
      $county = Input::get('county');
      $constituency = Input::get('constituency');
      $ward = Input::get('ward');

      switch ($location) {
        case 'By County':
        
        //process share report and organize by the selected
        $query =$county;
        $member_shares = DB::table('members')
                ->leftjoin('shares','members.member_registration_number',
                 'shares.member_number','=','members.member_registration_number')
                      ->select(DB::raw('name'),DB::raw('member_number'),
                            DB::raw('sum(amount) as total'))
                            ->where('members.county_id',$query)
                            ->where('shares.amount','!=',Null)
                ->orderBy(DB::raw('name'),'asc')
                ->groupBy(DB::raw('member_number'),DB::raw('name'))
                ->sharedLock()
                ->get();
                if(count($member_shares)==0)
                {
                  return Redirect::back()->withErrors('No results was found for your request')->withInput();
                }
                           
               $grandtotal = $member_shares->sum('total');
               //get title
              $county_name = County::where('id',$county)->first();

              $title = "Share Report by Members in ".$county_name->county_name." County";
              if(count($member_shares)==Null)
              {
                $message = "Currently there is no shares associated with ".$county_name->county_name;
                return redirect()->back()->with('Message',$message);
              }
                            
      return view('reports.shares.membershares',array('title'=>$title,
            'membershares'=>$member_shares,'grandtotal'=>$grandtotal));
          break;
          case 'By Constituency':
          $query = $constituency;

        $member_shares = DB::table('members')
                ->leftjoin('shares','members.member_registration_number',
                 'shares.member_number','=','members.member_registration_number')
                      ->select(DB::raw('name'),DB::raw('member_number'),
                            DB::raw('sum(amount) as total'))
                            ->where('members.constituency_id',$query)
                            ->where('shares.amount','!=',Null)
                ->orderBy(DB::raw('name'),'asc')
                ->groupBy(DB::raw('member_number'),DB::raw('name'))
                ->sharedLock()
                ->get();
                if(count($member_shares)==0)
                {
                  return Redirect::back()->withErrors('No results was found for your request')->withInput();
                }
                           
               $grandtotal = $member_shares->sum('total');
               //get title
              $const_name = Constituency::where('id',$constituency)->first();
              $title = "Share Report by Members in ".$const_name->const_name." Constituency";
              if(count($member_shares)==Null)
              {
                $message = "Currently there is no shares associated with ".$const_name->ward;
                return redirect()->back()->with('Message',$message);
              }
                            
      return view('reports.shares.membershares',array('title'=>$title,
            'membershares'=>$member_shares,'grandtotal'=>$grandtotal));
          break;
          case 'By Ward':
         $query = $ward;
      $member_shares = DB::table('members')
                ->leftjoin('shares','members.member_registration_number',
                 'shares.member_number','=','members.member_registration_number')
                      ->select(DB::raw('name'),DB::raw('member_number'),
                            DB::raw('sum(amount) as total'))
                            ->where('members.ward_id',$query)
                            ->where('shares.amount','!=',Null)
                ->orderBy(DB::raw('name'),'asc')
                ->groupBy(DB::raw('member_number'),DB::raw('name'))
                ->sharedLock()
                ->get();
                if(count($member_shares)==0)
                {
                  return Redirect::back()->withErrors('No results was found for your request')->withInput();
                }
                           
               $grandtotal = $member_shares->sum('total');
               //get title
              $ward_name = Ward::where('id',$ward)->first();
              $title = "Share Report by Members in ".$ward_name->ward." Ward";
              if(count($member_shares)==Null)
              {
                $message = "Currently there is no shares associated with ".$ward_name->ward;
                return redirect()->back()->with('Message',$message);
              }
                            
      return view('reports.shares.membershares',array('title'=>$title,
            'membershares'=>$member_shares,'grandtotal'=>$grandtotal));
          break;

        
        default:
         echo "Select Filter";
          break;
      }
      

  }

   public function SharePeriodicaReportByLocation()
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
       $location = Input::get('location');
      $county = Input::get('county');
      $constituency = Input::get('constituency');
      $ward = Input::get('ward');
      switch ($location) {
        case 'By County':
          # produce county report within the specified period
        $query =$county;
        $member_shares = DB::table('members')
      ->leftjoin('shares','members.member_registration_number',
                 'shares.member_number','=','members.member_registration_number')
      ->select(DB::raw('name'),DB::raw('member_number'),
                            DB::raw('sum(amount) as total'))
      ->where('members.county_id',$county)
      ->whereBetween('shares.date_paid',
                    [$start_date, $end_date])
       ->orderBy(DB::raw('name'),'asc')
                ->groupBy(DB::raw('member_number'),DB::raw('name'))
                ->sharedLock()
           ->paginate(20);

      $grandtotal = $member_shares->sum('total');
               //get title
              $county_name = County::where('id',$county)->first();
              $title = "Share Report by Members in ".$county_name->county_name." County ".$start_date." to ".$end_date;
if(count($member_shares)==Null)
              {
                $error = "Currently there is no shares associated with ".$county_name->county_name." county";
                return redirect()->back()->withErrors($error);
              }
                            
      return view('reports.shares.membershares',array('title'=>$title,
            'membershares'=>$member_shares,'grandtotal'=>$grandtotal));

          break;
          case 'By Constituency':
          #Process constituency report with the period
          $query = $constituency;
          $member_shares = DB::table('members')
      ->leftjoin('shares','members.member_registration_number',
                 'shares.member_number','=','members.member_registration_number')
      ->select(DB::raw('name'),DB::raw('member_number'),
                            DB::raw('sum(amount) as total'))
      ->where('members.constituency_id',$constituency)
      ->whereBetween('shares.date_paid',
                    [$start_date, $end_date])
       ->orderBy(DB::raw('name'),'asc')
                ->groupBy(DB::raw('member_number'),DB::raw('name'))
                ->sharedLock()
           ->paginate(20);

      $grandtotal = $member_shares->sum('total');
               //get title
              $const_name = Constituency::where('id',$constituency)->first();
              $title = "Share Report by Members in ".$const_name->const_name." Constituency ".$start_date." to ".$end_date;
if(count($member_shares)==Null)
              {
                $error = "Currently there is no shares associated with ".$const_name->const_name." constituency";
                return redirect()->back()->withErrors($error);
              }
                            
      return view('reports.shares.membershares',array('title'=>$title,
            'membershares'=>$member_shares,'grandtotal'=>$grandtotal));
           
          case 'By Ward':
          #Process Ward report in the specified period
          $query = $ward;
           $member_shares = DB::table('members')
      ->leftjoin('shares','members.member_registration_number',
                 'shares.member_number','=','members.member_registration_number')
      ->select(DB::raw('name'),DB::raw('member_number'),
                            DB::raw('sum(amount) as total'))
      ->where('members.ward_id',$ward)
      ->whereBetween('shares.date_paid',
                    [$start_date, $end_date])
       ->orderBy(DB::raw('name'),'asc')
                ->groupBy(DB::raw('member_number'),DB::raw('name'))
                ->sharedLock()
           ->paginate(20);

      $grandtotal = $member_shares->sum('total');
               //get title
              $ward_name = Ward::where('id',$ward)->first();
              $title = "Share Report by Members in ".$ward_name->ward." Ward ".$start_date." to ".$end_date;
if(count($member_shares)==Null)
              {
                $error = "Currently there is no shares associated with ".$ward_name->ward." ward";
                return redirect()->back()->withErrors($error);
              }
                            
      return view('reports.shares.membershares',array('title'=>$title,
            'membershares'=>$member_shares,'grandtotal'=>$grandtotal));
        
        default:
          # code...
        return "No location was specified";
          break;
      }

     
       
    }



    
}
