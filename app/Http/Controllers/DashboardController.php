<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Report;
use App\Member;
use App\Share;
use DB;
use Carbon\Carbon;
use Validator;
use PDF;
use Excel;
use App\County;
use Khill\Lavacharts\Lavacharts;
use Charts;
use Auth;


class DashboardController extends Controller
{
    //
  public function __construct()
  {
    $this->middleware('auth');

  }
    public function countMembers()
    {
    	$memberCount = DB::table('members')->get()->count();
    	return $memberCount;

    }

     public function countMaleMembers()
    {
    	$maleMemberCount = DB::table('members')
    	->where('sex','Male')
    	->get()->count();
    	return $maleMemberCount;

    }

       public function countFemaleMembers()
    {
    	$femaleMemberCount = DB::table('members')
    	->where('sex','Female')
    	->get()->count();
    	return $femaleMemberCount;

    }

      public function MembersRegisteredToday()
    {
    	$now = Carbon::now();
        $startOfThisDay = Carbon::instance($now)->startOfDay();
        $currentOfThisDay = Carbon::instance($now)->subSecond();
        $query = DB::table('members')
        ->whereBetween('created_at',[$startOfThisDay,$currentOfThisDay])
        ->get()
        ->count();
        return $query;
    }
    public function MembersRegisteredThisWeek()
    {
    	$now = Carbon::now();
        $startOfThisWeek = Carbon::instance($now)->startOfWeek();
        $currentOfThisWeek = Carbon::instance($now)->subSecond();
        $query = DB::table('members')
        ->whereBetween('created_at',[$startOfThisWeek,$currentOfThisWeek])
        ->get()
        ->count();
        return $query;
    }

     public function MembersRegisteredThisMonth()
    {
        $now = Carbon::now();
        $startOfThisMonth = Carbon::instance($now)->startOfMonth();
        $currentOfThisMonth = Carbon::instance($now)->subSecond();

  
        		$query = DB::table('members')
        			
        			->whereBetween('created_at',[$startOfThisMonth,$currentOfThisMonth])
        			->get()->count();
        			return $query;
        		}

        		 public function MembersRegisteredThisYear()
    {
        $now = Carbon::now();
        $startOfThisYear = Carbon::instance($now)->startOfYear();
        $currentOfThisYear = Carbon::instance($now)->subSecond();

  
        		$query = DB::table('members')
        			
        			->whereBetween('created_at',[$startOfThisYear,$currentOfThisYear])
        			->get()->count();
        			return $query;
        		}

        		 public function CountYouthMembers()
        		 {
        		 	//List all members with their basic information
    	$memberlist= Member::all();
      $youthMembers =0;
    	foreach ($memberlist as  $member) {

    		$age = Carbon::now()->diffInYears($member->year_of_birth);
        if($age<=35)
        {
          $youthMembers++;
        }
    	
    	}
      return $youthMembers;

        		 }

      public function totalShares()
      {
      $total_shares = DB::table('members')
    						->leftjoin('shares','members.member_registration_number',
    						 'shares.member_number','=','members.member_registration_number')
                           ->select(DB::raw('name'),DB::raw('member_number'),DB::raw('sum(amount) as total'))
                            ->where('shares.amount','!=',Null)
    						->orderBy(DB::raw('name'),'asc')
    						->groupBy(DB::raw('member_number'),DB::raw('name'))
    						->sharedLock()
    						->get();
      	$total_shares = $total_shares->sum('total');
      		return $total_shares;
      }
    
     public function sharesReceivedToday()

      {

      	$now = Carbon::now();
        $startOfThisDay = Carbon::instance($now)->startOfDay();
        $currentOfThisDay = Carbon::instance($now)->subSecond();

      $total_shares = DB::table('members')
    						->leftjoin('shares','members.member_registration_number',
    						 'shares.member_number','=','members.member_registration_number')
                           ->select(DB::raw('name'),DB::raw('member_number'),DB::raw('sum(amount) as total'))
                            ->whereBetween('shares.created_at',[$startOfThisDay,$currentOfThisDay])
    						->orderBy(DB::raw('name'),'asc')
    						->groupBy(DB::raw('member_number'),DB::raw('name'))
    						->sharedLock()
    						->get();
      	$total_shares_today = $total_shares->sum('total');
      		return $total_shares_today;
      }


       
       public function sharesReceivedThisWeek()

      {

      	$now = Carbon::now();
        $startOfThisWeek = Carbon::instance($now)->startOfWeek();
        $currentOfThisWeek = Carbon::instance($now)->subSecond();

      $total_shares = DB::table('members')
    						->leftjoin('shares','members.member_registration_number',
    						 'shares.member_number','=','members.member_registration_number')
                           ->select(DB::raw('name'),DB::raw('member_number'),DB::raw('sum(amount) as total'))
                            ->whereBetween('shares.created_at',[$startOfThisWeek,$currentOfThisWeek])
    						->orderBy(DB::raw('name'),'asc')
    						->groupBy(DB::raw('member_number'),DB::raw('name'))
    						->sharedLock()
    						->get();
      	$total_shares_this_week = $total_shares->sum('total');
      		return $total_shares_this_week;
      }

      public function sharesReceivedThisMonth()

      {

      	$now = Carbon::now();
        $startOfThisMonth = Carbon::instance($now)->startOfMonth();
        $currentOfThisMonth = Carbon::instance($now)->subSecond();

      $total_shares = DB::table('members')
    						->leftjoin('shares','members.member_registration_number',
    						 'shares.member_number','=','members.member_registration_number')
                           ->select(DB::raw('name'),DB::raw('member_number'),DB::raw('sum(amount) as total'))
                            ->whereBetween('shares.created_at',[$startOfThisMonth,$currentOfThisMonth])
    						->orderBy(DB::raw('name'),'asc')
    						->groupBy(DB::raw('member_number'),DB::raw('name'))
    						->sharedLock()
    						->get();
      	$total_shares_month = $total_shares->sum('total');
      		return $total_shares_month;
      }

      public function sharesByConstituency()
      {
        
        $constituencies = DB::table('constituencies')
                        ->leftjoin('members','members.constituency_id','constituencies.id','constituencies.id')
                        ->leftjoin('shares','members.member_registration_number','shares.member_number','members.member_registration_number')
                        ->join('counties','constituencies.county_id','counties.id','constituencies.county_id')
                        ->select(DB::raw('const_name'),
                                 DB::raw('sum(shares.amount) as share'))
                        ->where('constituencies.county_id',42)

                        ->groupBy(DB::raw('const_name'))
                        ->get();
                        return $constituencies;

      }

      public function sharesInWards()
      {
        $wards = DB::table('wards')
                  ->join('members','members.ward_id','wards.id','members.ward_id')
                  ->join('shares','members.member_registration_number','shares.member_number','members.member_registration_number')
                  ->join('constituencies','wards.constituency_id','constituencies.id','wards.constituency_id')
                  ->join('counties','constituencies.county_id','counties.id','constituencies.county_id')
                  ->select(DB::raw('ward'),
                                 DB::raw('sum(shares.amount) as share'))
                  ->where('counties.id',42)
                  ->groupBy(DB::raw('ward'))
                  ->get();
                  return $wards;
      }

      public function sharesReceivedThisYear()

      {

      	$now = Carbon::now();
        $startOfThisYear = Carbon::instance($now)->startOfYear();
        $currentOfThisYear = Carbon::instance($now)->subSecond();

      $total_shares = DB::table('members')
    						->leftjoin('shares','members.member_registration_number',
    						 'shares.member_number','=','members.member_registration_number')
                           ->select(DB::raw('name'),DB::raw('member_number'),DB::raw('sum(amount) as total'))
                            ->whereBetween('shares.created_at',[$startOfThisYear,$currentOfThisYear])
    						->orderBy(DB::raw('name'),'asc')
    						->groupBy(DB::raw('member_number'),DB::raw('name'))
    						->sharedLock()
    						->get();
      	$total_shares_year = $total_shares->sum('total');
      		return $total_shares_year;
      }

      public function topFiveShareholders()
      {
      	$topFiveShareholders = DB::table('members')
    						->leftjoin('shares','members.member_registration_number',
    						 'shares.member_number','=','members.member_registration_number')
                           ->select(DB::raw('name'),DB::raw('member_number'),DB::raw('sum(amount) as total'))
                            ->where('shares.amount','!=',Null)
    						->orderBy(DB::raw('total'),'desc')
    						->groupBy(DB::raw('member_number'),DB::raw('name'))
    						->sharedLock()
    						->take(5)
    						->get();
      	//$total_shares_year = $total_shares->sum('total');
      		return $topFiveShareholders;
      }
      public function membersByContstituency()
      {
        $constMembers = DB::table('members')
                        ->join('constituencies','members.constituency_id','constituencies.id','members.constituency_id')
                        ->join('counties','constituencies.county_id','counties.id','constituencies.county_id')
                        ->select(DB::raw('const_name'),DB::raw('count(members.id) as members'))
                        ->where('constituencies.county_id',42)
                        ->groupBy(DB::raw('const_name'))->get();
                        return $constMembers;

      }

      public function bottomFiveShareholders()
      {
      	$bottomFiveShareholders = DB::table('members')
    						->leftjoin('shares','members.member_registration_number',
    						 'shares.member_number','=','members.member_registration_number')
                           ->select(DB::raw('name'),DB::raw('member_number'),DB::raw('sum(amount) as total'))
                            ->where('shares.amount','!=',Null)
    						->orderBy(DB::raw('total'),'asc')
    						->groupBy(DB::raw('member_number'),DB::raw('name'))
    						->sharedLock()
    						->take(5)
    						->get();
      	//$total_shares_year = $total_shares->sum('total');
      		return $bottomFiveShareholders;
      }

      //Land statistics 

      public function MemberByLandAcreage()
{

	$land_acres = DB::table('members')
				->select(DB::raw('name'),DB::raw('sub_county'),DB::raw('landsize'),
					DB::raw('sum(landsize) as total_land'))
				->groupBy(DB::raw('sub_county'),DB::raw('name'),DB::raw('landsize'))
				->orderBy(DB::raw('name','asc'))
				//->sharelock()
				->get();
					$total_land = $land_acres->sum('total_land');
					return $total_land;

			}

			public function LandOwnedByMen()
{

	$land_acres = DB::table('members')
        ->select(DB::raw('name'),DB::raw('sub_county'),DB::raw('landsize'),
          DB::raw('sum(landsize) as total_land'))
            ->where('sex','=','Male')
        ->groupBy(DB::raw('sub_county'),DB::raw('name'),DB::raw('landsize'))
        ->orderBy(DB::raw('name','asc'))
        //->sharelock()
        ->get();
          $total_land = $land_acres->sum('total_land');
          return $total_land;


  

			}
			public function LandOwnedByWomen()
{

	$land_acres = DB::table('members')
				->select(DB::raw('name'),DB::raw('sub_county'),DB::raw('landsize'),
					DB::raw('sum(landsize) as total_land'))
				->where('sex','=','Female')
				->groupBy(DB::raw('sub_county'),DB::raw('name'),DB::raw('landsize'))
				->orderBy(DB::raw('name','asc'))
				//->sharelock()
				->get();
					$total_land = $land_acres->sum('total_land');
					return $total_land;

			}

      public function PercentageLandOwnedByMen()
      {
        $men_perce_land = ($this->LandOwnedByMen()/ $this->MemberByLandAcreage())*100;
        return $men_perce_land;

      }
      public function PercentageLandOwnedByWomen()
      {
        $women_perce_land = ($this->LandOwnedByWomen()/ $this->MemberByLandAcreage())*100;

         return $women_perce_land;

      }

	public function topFiveLandOwners()
{

	$topFiveLandOwners = DB::table('members')
           -> select(
                      DB::raw('name'),
                    DB::raw('member_registration_number'),
                    DB::raw('sum(landsize) as land')
                    )
          ->groupBy('member_registration_number','name')
				->orderBy('land','DESC')
				->take(5)
				->get();
       // print_r($topFiveLandOwners);exit;
					return $topFiveLandOwners;
        

			}
      

    public function Dashboard()
    {
    	$memberCount = $this->countMembers();
    	$maleMemberCount = $this->countMaleMembers();
    	$femaleMemberCount = $this->countFemaleMembers();
    	$membersRegisteredToday = $this->MembersRegisteredToday();
    	$membersRegisteredThisWeek= $this->MembersRegisteredThisWeek();
    	$MembersRegisteredThisMonth = $this->MembersRegisteredThisMonth();
    	$membersRegisteredThisYear = $this->MembersRegisteredThisYear();
    	
      $data = DB::table('members')
      ->select(DB::raw('count(id) as data'),DB::raw('YEAR(created_at) year, MONTH(created_at) month'))
      ->groupBy('year','month')->get();


      return view('dashboard',array('total_members'=>$memberCount,
    		'male_members'=>$maleMemberCount,
    		'female_members'=>$femaleMemberCount,
    		'membersRegisteredThisYear'=>$membersRegisteredThisYear,
    		'membersRegisteredThisMonth'=>$MembersRegisteredThisMonth,
    		'membersRegisteredThisWeek'=>$membersRegisteredThisWeek,
    		'membersRegisteredToday'=>$membersRegisteredToday,
    		'total_shares'=>$this->totalShares(),
    		'total_shares_today'=>$this->sharesReceivedToday(),
    		'total_shares_this_week'=>$this->sharesReceivedThisWeek(),
    		'total_shares_this_month'=>$this->sharesReceivedThisMonth(),
    		//'total_shares_this-quarter'=>$this->sharesReceivedThisQuarter(),
    		'total_shares_this_year'=>$this->sharesReceivedThisYear(),
    		'topFiveShareholders'=>$this->topFiveShareholders(),
    		'bottomFiveShareholders'=>$this->bottomFiveShareholders(),
    		'total_land'=>$this->MemberByLandAcreage(),
    		'land_owned_by_men'=>$this->LandOwnedByMen(),
    		'land_owned_by_women'=>$this->LandOwnedByWomen(),
    		'topFiveLandOwners'=>$this->topFiveLandOwners(),
        'PerLandOwnedByMen'=>$this->PercentageLandOwnedByMen(),
        'PerLandOwnedByWomen'=>$this->PercentageLandOwnedByWomen(),'data'=>$data,'constituencies'=>$this->sharesByConstituency(),'wards'=>$this->sharesInWards(),'constMembers'=>$this->membersByContstituency(),'youthMembers'=>$this->CountYouthMembers(),'user'=>$this->userProfile()));

    }
    public function memberListIndex()
    {
      return view('reports.members.index',array('title'=>'Members Related Reports'));
    }

    public function listCounties()

    {
      $counties = County::with('constituencies')       
        ->get();
      //  var_dump($counties);exit;
      return view('reports.locations.counties',array('counties'=>$counties));
    }

    public function userProfile()
    {
      $user = DB::table('users')
              ->join('userprofiles','users.id','users.id','usersprofiles.user_id')
              ->where('users.id',Auth::User()->id)->first();
              return $user;
    }}
