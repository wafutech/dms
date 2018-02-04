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
use App\MembersGrainInventoryCategory;
use App\Mail\ShareStatement;
use Mail;

class PDFExportController extends Controller
{
    //
  var $counties;
    var $reports;
    var $report_title = "Nzoia Grain Marketing and Processing Co-operative society";
    public function __construct()
    {
      $this->middleware('role:admin');
      $this->counties = County::pluck('county_name','id');
     //download members list in PDF form
    }
    public function MemberListToPDF()
    {
        //$memberlist= Member::all();
      $memberlist= DB::table('members')
        ->leftjoin('counties','county_id',
          'counties.id','members.county_id')
           ->leftjoin('constituencies','constituency_id',
          'constituencies.id','members.constituency_id')
        ->leftjoin('education_levels','education_level',
          'education_levels.id','members.education_level')
        /*->leftjoin('users','user_id',
          'users.id','members.user_id')*/
        ->leftjoin('certificates','member_registration_number',
          'certificates.member_number','members.member_registration_number')
         ->leftjoin('wards','ward_id',
          'wards.id','members.ward_id')
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
            $malePercentage = round((count($male_members)/count($memberlist))*100,2);
           $femalePercentage = round((count($female_members)/count($memberlist))*100,2);
        $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');

     $pdf = PDF::loadView('reports.members.memberlist', array('memberlists'=>$memberlist,'title'=>'List of Members',
      'male_members'=>$male_members,'female_members'=>$female_members,
      'malePercentage'=>$malePercentage,'femalePercentage'=>$femalePercentage,
      'counties'=>$this->counties))->setOption('orientation','Landscape')->setOption('footer-left','List of members')->setOption('header-center','Nzoia Grain Processing and Marketing Cooperative Society')->setOption('header-font-size',8)->setOption('footer-font-size',8)
     ->setOption('footer-center','page [page] of [toPage]')->setOption('page-size','A4')->setOption('margin-top',5)->setOption('margin-bottom',4)->setOption('margin-left',4)->setOption('margin-right',4);
$filename = "memberlist".date('d-m-Y');
return $pdf->download($filename.'.pdf');

    }

    //Export member contact list to PDF
    public function MembersContactlist()
    {
    $MembersContactlist = DB::table('members')
                        ->select(DB::raw('name'),DB::raw('sex'),DB::raw('phone_contact'),DB::raw('email'),
                          DB::raw('postal_address'),DB::raw('town'),
                          DB::raw('postal_code'),DB::raw('phisical_address'))
                          ->orderBy('name','asc')
                          ->get();
              $header_text = 'Nzoia Grain Marketing and Processing Cooperative society members contact list';
                     $footer_date = Carbon::Parse(Carbon::now())->format('M d, Y');
  $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');    
    $pdf = PDF::loadView('reports.members.membercontactlist',array('memberlists'=>$MembersContactlist,
      'title'=>'Member Contacts'))->setOption('header-center',$header_text)->setOption('header-font-size',8)->setOption('footer-left',$footer_date)->setOption('footer-center','page [page] of [toPage]')->setOption('footer-font-size',8)->setOption('footer-right','Contact List');
    return $pdf->download('contact list'.'.pdf');

    }

    public function MembersListBySexPDF()
    {
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
        ->leftjoin('education_levels','education_level',
          'education_levels.id','members.education_level')
        ->leftjoin('certificates','member_registration_number',
          'certificates.member_number','members.member_registration_number')
        ->leftjoin('users','user_id',
          'users.id','members.user_id')        
      ->where('sex',$sex)->paginate(10);

  $title = $sex." "."Members";
  $count = count($memberlists);

  if($count ==Null)
  {
    $message = "No results for ".$sex." were found";
  return  Redirect::back()->with('message',$message)->withInput();

  }
  else
  {
    $filename =$sex." members";
  $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');    
  
  $pdf = PDF::loadView('reports.members.memberlistBySex',array('memberlists'=>$memberlists,
     'title'=>$title,'total'=>$count));
  return $pdf->download($filename.'.pdf');

   


}
    }

   

    public function registrationCertificate()
    {
      return view('docs.certificate');
    }

    public function registrationReceipt()
    {
      return view('docs.registrationReceipt');
    }

    public function shareStatementPDFform()
{
  return view('reports.members.memberstatementformPDF',
    array('title'=>'PDF Version Share Statement'
                    ));

}

    public function shareStatementPDF()
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
      
              $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');
              //set footer text
              $date = Carbon::Parse(Carbon::now())->format('d-m-Y');
              $footer_text = 'This statement was printed on '.$date.'. Complains arising from this statement should be comunicated to the Cooperative immediately within 14 days';
              $header_text = 'Share statement for '.$member->name.' of account number '.$member->member_registration_number;

              //Prepare PDF version of statement
  $pdf =PDF::loadView('reports.members.memberstatement',array('title'=>$this->report_title,
     'membershares'=>$query,'accountnumber'=>$number,'start_date'=>$start_date,
          'end_date'=>$end_date,'member'=>$member,'total'=>$subtotal))->setOption('footer-center',$footer_text)->setOption('footer-font-size',6)->setOption('header-center',$header_text)->setOption('header-right','page [page] of [toPage]')->setOption('header-font-size',8);
    $filename = "sharestatement".date('d-m-Y');
              
 

if(Input::get('statementAction')=='email')
{
  //Fetch the member's email address
  $member = Member::where('member_registration_number',$number)->first();
 
  //Check if the member has an email address
  if($member->email==null)
  {
    //Flash an error message and quit for missing email address. Push the user to select download option instate. 
    return Redirect::back()->withErrors('This member do not have an email address, choose download option instead')->withInput();
  }
  //If false, proceed to email the share statement to the member
  $statement = $pdf;
  Mail::to($member->email)->send(new ShareStatement($member,$statement,$filename));

}
//Check if the user selected both actions (Download and Email)
elseif(Input::get('statementAction')=='both')
{
  $statement = $filename.'.pdf';
  Mail::to($member->email)->send(new ShareStatement($member,$statement));
  return $pdf->download($filename.'.pdf');


}
else
{
  //Else downlaod the statement       
       
return $pdf->download($filename.'.pdf');
}



    }

    //Export Share by members report to PDF

    public function memberSharesToPDF()
    {
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
                ->paginate(3000);
                           
                         $grandtotal = $member_shares->sum('total');
                  $header_text = 'Nzoia Grain Marketing and Processing Cooperative society members shares Report';
                     $footer_date = Carbon::Parse(Carbon::now())->format('M d, Y');

              $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');
                
          $pdf =PDF::loadView('reports.shares.membershares',array('title'=>$this->report_title,
            'membershares'=>$member_shares,'grandtotal'=>$grandtotal))->setOption('header-center',$header_text)->setOption('header-font-size',8)->setOption('footer-left',$footer_date)->setOption('footer-center','page [page] of [toPage]')->setOption('footer-font-size',8)->setOption('footer-right','Share Monthly Report');
              $filename = "Shareholder_summary".date('d-m-Y');
              return $pdf->download($filename.'.pdf');

    }

    //export list of counties with its constituencies to PDF
    public function listCountiesPDF()
    {
      $counties = County::with('constituencies')       
        ->get();
        $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');

      //  var_dump($counties);exit;
      $pdf = PDF::loadView('reports.locations.counties',array('counties'=>$counties));
            $filename ='CountiesWithConstituencies';     
         return $pdf->download($filename.'.pdf');
       }

         //Share monthly report pdf
         public function ShareCurrentMonthReportPDF()
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
                     $header_text = 'Nzoia Grain Marketing and Processing Cooperative society members share deposit in the month of '. $monthString.' '.$month[0];
                     $footer_date = Carbon::Parse($now)->format('M d, Y');
               $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');

        $pdf = PDF::loadView('reports.shares.sharemonthlyreport',array('title'=>$title,
                    'membershares'=>$query,'grandtotal'=>$grandtotal))->setOption('header-center',$header_text)->setOption('header-font-size',8)->setOption('footer-left',$footer_date)->setOption('footer-center','page [page] of [toPage]')->setOption('footer-font-size',8)->setOption('footer-right','Share Monthly Report');
                $filename = $currentOfThisMonth;
                 return $pdf->download($filename.'.pdf');


    }

    public function MemberTodaysSharesPDF()
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
                      $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');

        $pdf = PDF::loadView('reports.shares.shareDailyreport',array('title'=>$title,
                    'membershares'=>$query,'grandtotal'=>$grandtotal));
                  $filename = 'todaysShareReport'.$currentOfThisDay;

              return $pdf->download($filename.'.pdf');

    }

   public function MemberCurrentWeekSharesPDF()
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
        $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');

        $pdf = PDF::loadView('reports.shares.shareWeeklyreport',array('title'=>'This Week Share Report',
                    'membershares'=>$query,'grandtotal'=>$grandtotal));
      $filename = 'thisWeekShareReport'.$currentOfThisWeek;

              return $pdf->download($filename.'.pdf');
    }

    public function MemberCurrentYearSharesPDF()
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
               $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');

       $pdf = PDF::loadView('reports.shares.shareAnualReport',array('title'=>'This Year Share Report by Member',
                    'membershares'=>$query,'grandtotal'=>$grandtotal));
     $filename = 'thisYearShareReport'.$currentOfThisYear;

              return $pdf->download($filename.'.pdf');
    }

    public function MemberCurrentQuarterSharesPDF()
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
                      $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');

       $pdf = PDF::loadView('reports.shares.shareQuarterlyReport',array('title'=>'This Quarter Share Report by Member',
                    'membershares'=>$query,'grandtotal'=>$grandtotal));
 $filename = 'thisQuarterShareReport'.$currentOfThisQuarter;

              return $pdf->download($filename.'.pdf');

    }

    public function membersByLandPDF(Request $request)
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
        $total_land = $land_acres->sum('landsize');
        $grandtotal = $land_acres->sum('total');
         $header_text = 'Nzoia Grain Marketing and Processing Cooperative society members Land Land Report';
                     $footer_date = Carbon::Parse(Carbon::now())->format('M d, Y');

        $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');

        $pdf = PDF::loadView('reports.land.memberland',array('title'=>'Members land by County',
                    'memberlands'=>$land_acres,'grandtotal'=>$grandtotal,'counties'=>$this->counties,'total_land'=>$total_land))->setOption('header-center',$header_text)->setOption('header-font-size',8)->setOption('footer-left',$footer_date)->setOption('footer-center','page [page] of [toPage]')->setOption('footer-font-size',8)->setOption('footer-right','Share Custom Report');
      $filename = 'membersbyland';

              return $pdf->download($filename.'.pdf');
    }

   public function MemberLandAndSharesPDF()
   {
    $member_shares = DB::table('members')
                ->leftjoin('shares','members.member_registration_number',
                 'shares.member_number','=','members.member_registration_number')
                ->leftjoin('counties','members.county_id','counties.id','members.county_id')
                           ->select(DB::raw('name'),DB::raw('landsize'),DB::raw('member_registration_number'),
                            DB::raw('county_name'),DB::raw('sum(amount) as total'))
                         
                ->orderBy(DB::raw('county_name'),'asc')
                ->groupBy(DB::raw('member_registration_number'),DB::raw('county_name'),DB::raw('name'),DB::raw('landsize'))
                ->sharedLock()
                ->get();
                $total_land =$member_shares->sum('landsize');          
             $grandtotal = $member_shares->sum('total');
             $header_text = 'Nzoia Grain Marketing and Processing Cooperative society members Land Land Report';
                     $footer_date = Carbon::Parse(Carbon::now())->format('M d, Y');

             $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');
           $pdf= PDF::loadView('reports.shares.membersharewithland',array('title'=>'Members Share with Land',
                    'members'=>$member_shares,'grandtotal'=>$grandtotal,'counties'=>$this->counties,'total_land'=>$total_land))->setOption('header-center',$header_text)->setOption('header-font-size',8)->setOption('footer-left',$footer_date)->setOption('footer-center','page [page] of [toPage]')->setOption('footer-font-size',8)->setOption('footer-right','Share Custom Report');
           $filename='MemberSharesAndLand';
        return $pdf->download($filename.'.pdf');
   }

    public function MemberSharesDetailReportPDF()
    {
      $members = Member::with('Share')->get();
       $header_text = 'Nzoia Grain Marketing and Processing Cooperative society- Members\' Share Report';
                     $footer_date = Carbon::Parse(Carbon::now())->format('M d, Y');
              $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');

      $pdf = PDF::loadView('reports.shares.memberWithshares',
        array('title'=>'Share Details Report',
          'members'=>$members))->setOption('header-center',$header_text)->setOption('header-font-size',8)->setOption('footer-left',$footer_date)->setOption('footer-center','page [page] of [toPage]')->setOption('footer-font-size',8)->setOption('footer-right','Share Details Report');
      $filename='membershareDetailsReport';
        return $pdf->download($filename.'.pdf');


    }

    public function exportMembersInventoryTodPDF()
    {
       $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');
       $members = Member::with('inventory')     
      ->get();
       $pdf =PDF::loadView('reports.inventory.membersWithInventory',array('members'=>$members,'title'=>'Members With Inventory'));
    $filename = "Member With Inventory ".date('d-m-Y');
return $pdf->download($filename.'.pdf');
    }

    public function exportCategoryInventoryTodPDF()
    {
      $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');
        $categories = MembersGrainInventoryCategory::with('inventory')
           ->get();
           $pdf= PDF::loadView('reports.inventory.categoriesWithInventory',array('members'=>$categories,'title'=>'Categories With Inventory'));
    
    $filename = "Category With Inventory ".date('d-m-Y');
return $pdf->download($filename.'.pdf');
    }

 public function ShareholderCustomDetailReportForm()
    {
      return view('reports.shares.sharePeriodicReportsFormPDF',['title'=>'Custom Periodic Report']);
    }
    public function ShareholderCustomDetailReportPDF()

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

                 $header_text = 'Nzoia Grain Marketing and Processing Cooperative society members shares Report';
                     $footer_date = Carbon::Parse(Carbon::now())->format('M d, Y');

              $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');
            
    $filename = "SharecustomReport ".date('d-m-Y');
       
        $pdf = PDF::loadView('reports.shares.sharecustomreports',array('title'=>'Share Custom Report',
                    'membershares'=>$query,'grandtotal'=>$grandtotal,'start_date'=>$start_date,
                    'end_date'=>$end_date))->setOption('header-center',$header_text)->setOption('header-font-size',8)->setOption('footer-left',$footer_date)->setOption('footer-center','page [page] of [toPage]')->setOption('footer-font-size',8)->setOption('footer-right','Share Custom Report');
        return $pdf->download($filename.'.pdf');


                      

    }
}
