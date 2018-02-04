<?php namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Event;
use App\Events\MemberRegistrationEvent;
use App\Events\ImportMembersEvent;
use App\Events\ImportSharesEvent;
use App\Report;
use App\Member;
use App\Share;
use DB;
use Session;
use Carbon\Carbon;
use Validator;
use PDF;
use Excel;
use App\County;
use App\MembersGrainInventoryCategory;
use App\Http\CustomClass\MembershipNumberGenerator;
use Auth;


class ExcelExportImportController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
    $this->middleware('role:admin');
    $this->counties =  County::pluck('county_name','id');

  }
    //
    //export list to microsoft excel format
   public function MemberlistExportToExcel()
    {
        $memberlist= Member::all();              
               

Excel::create('memberslist',
 function($excel) use($memberlist) 
 {
    $excel->sheet('List of Members', function($sheet) use($memberlist) 
    {
        $sheet->fromArray($memberlist);
    });
})->export('xls');
}

public function memberlistviewToExcel()
{
//export view
Excel::create('memberlist',
function($excel)
{
    $excel->sheet('Member List ',
        function($sheet)
        {
             $memberlist= DB::table('members')
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
        ->get();

      $female_members = $memberlist->filter(
            function($female)
            {
              //use (memberlist);
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
         
                 
           $sheet->loadView('reports.msexcel.memberlistExcel', array('memberlists'=>$memberlist,'title'=>'title',
      'male_members'=>$male_members,'female_members'=>$female_members,
      'malePercentage'=>$malePercentage,'femalePercentage'=>$femalePercentage,'counties'=>$this->counties));
        });

})->export('xml');

}
public function ExportSharesToExcel()
{
  $shares = Share::all();

  Excel::create('shares',
 function($excel) use($shares) 
 {
    $excel->sheet('shares', function($sheet) use($shares) 
    {
        $sheet->fromArray($shares);
    });
})->export('xls');

}
public function ImportSharesForm()
{
  return view('shares.importFromExcel');

}

public function ImportSharesFromExcel()
{
  try
  {
   if(Input::hasFile('import_file')){
      $path = Input::file('import_file')->getRealPath();
      $data = Excel::load($path, function($reader) {
      })->get();
      if(!empty($data) && $data->count()){
        foreach ($data as $key => $value) {
          //validate username and receipt numbers
          $validation_rules = array(
          'member_number'         => 'numeric|digits:4|exists:members,member_registration_number',
          'amount'         => 'numeric',
          'receipt_no'      => 'numeric|unique:shares|exists:receipt_numbers,number', 
          'date_paid'      => 'date',       
          
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
      if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      } 
          
          $insert[] = [
          'member_number' => $value->member_number,
           'amount' => $value->amount,
           'receipt_no'=>$value->receipt_no,
           'user_id'=>Auth::user()->id,
           'date_paid'=>Carbon::parse($value->date_paid),
           'imported'=>1,
           'created_at'=>Carbon::now(),
           'updated_at'=>Carbon::now()];          
        }
        if(!empty($insert)){
          DB::table('shares')->insert($insert);
   Event::Fire(new ImportSharesEvent());

return Redirect::route('shares.create')->with('message','Shares imported successfully');        }
      }
    }
    return back(); 
  }
  catch(Exception $e)
  {
    return $e->getMessage();
  }
  

}

//Exoport member shares view to excel
public function ShareholderSummary()
{
  Excel::create('Member Shares',
function($excel)
{
    $excel->sheet('Member shares',
        function($sheet)
        {
      $member_shares = DB::table('members')
                ->leftjoin('shares','members.member_registration_number',
                 'shares.member_number','=','members.member_registration_number')
                           ->select(DB::raw('name'),DB::raw('member_number'),DB::raw('sum(amount) as total'))
                            ->where('shares.amount','!=',Null)
                ->orderBy(DB::raw('name'),'asc')
                ->groupBy(DB::raw('member_number'),DB::raw('name'))
                ->sharedLock()
                ->paginate(30);
                           
                         $grandtotal = $member_shares->sum('total');

                            
      $sheet->loadView('reports.msexcel.membershares',array('title'=>$this->report_title,
            'membershares'=>$member_shares,'grandtotal'=>$grandtotal));
              
        });
})->export('xls');


}

public function MemberLandAndShares()
    {

      
   Excel::create('Member Shares With Land',
function($excel)
{
    $excel->sheet(' shares and Land',
        function($sheet)
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
                //print_r($member_shares);exit;
                           
             $grandtotal = $member_shares->sum('total');
           $sheet->loadView('reports.msexcel.membersharewithland',array('title'=>'Members Share with Land',
                    'members'=>$member_shares,'grandtotal'=>$grandtotal,'counties'=>$this->counties));  
              
        });
})->export('xls');

    }
    public function MembersContactlist()
    {
    Excel::create('Contact List',
function($excel)
{
    $excel->sheet(' Contact List',
        function($sheet)
        {
      $MembersContactlist = DB::table('members')
                        ->select(DB::raw('name'),DB::raw('sex'),DB::raw('phone_contact'),DB::raw('email'),
                          DB::raw('postal_address'),DB::raw('town'),
                          DB::raw('postal_code'),DB::raw('phisical_address'))
                          ->orderBy('name','asc')
                          ->get();
    
   $sheet->loadView('reports.msexcel.membercontactlist',array('memberlists'=>$MembersContactlist,
      'title'=>'Member Contacts'));
              
        });
})->export('xls');

    }

//Export contact list view to xls
public function contactlistviewToExcel()
{

Excel::create('contact list',
	function($excel)
	{
		$excel->sheet('contact list',
			function($sheet)
			{
				$sheet->setOrientation('landscape');
			});
	})->export('xls');

}


public function membersBySexExportToExcel()
{
	//export view
Excel::create('membersbysex',
function($excel)
{
    $excel->sheet('new sheet',
        function($sheet)
        {
              $memberlist= New ReportsController;
              $memberlist = $memberlist->MembersListBySex();
            $sheet->loadView('reports.members.memberlistbysex',array('title'=>'List of Members','memberlists'=>$memberlist));
        });
});

}
//Memberlist from excel into members database
public function MemberImportForm()
{
	return view('members.import_members_from_excel',array('title'=>'Import From Excel'));
}
public function importMembers()
	{


       
$user = Auth::user()->id;
        
		if(Input::hasFile('excelfile')){
      $path = Input::file('excelfile')->getRealPath();
      $data = Excel::load($path, function($reader) {
      })->get();
      if(!empty($data) && $data->count()){
        foreach ($data as $key => $value) {
          
       try
    {
     $insert[] = ['name' => $value->name, 
          'year_of_birth' => Carbon::Parse($value->year_of_birth),
          'sex'=>$value->sex,
          'landsize'=>$value->landsize,
          'idnumber'=>$value->idnumber,
          'phone_contact'=>$value->phone_contact,
          'email'=>$value->email,
          'postal_address'=>$value->postal_address,
          'town'=>$value->town,
          'postal_code'=>$value->postal_code,
          'phisical_address'=>$value->phisical_address,
          'county_id'=>$value->county_id,
          'sub_county'=>$value->sub_county,
          'constituency_id'=>$value->constituency_id,
          'ward_id'=>$value->ward_id,
          'education_level'=>$value->education_level,
          'occupation'=>$value->occupation,
          'registration_fee'=>$value->registration_fee,
          'receipt_no'=>$value->receipt_no,
          'member_registration_number'=>rand(1000,99990),
          'user_id'=>$user,
          'registration_date'=>$value->registration_date,
          'imported'=>1]; 

          
    }
    catch(Exception $e)
    {
      $e->getMessage();
    }          
          
        }
        if(!empty($insert)){
          DB::table('members')->insert($insert);
 //Create registration certicate and prepare registration receipts for just imported members
          Event::fire(new ImportMembersEvent());

 return Redirect::route('members.create')->with('message','Records imported successfully');
        }
      }
    }
    
  }

  //Export inventory Reports to Excel
  public function exportMembersInventoryToExcel()
  {

    Excel::create('Member Shares',
function($excel)
{
    $excel->sheet('Members With Inventory',
        function($sheet)
        {
      $members = Member::with('inventory')
      
      ->get();
                            
      $sheet->loadView('reports.inventory.membersWithInventory',array('members'=>$members,'title'=>'Members With Inventory'));
              
        });
})->export('xls');
  }

  //Export Grain Categories with inventory to excel
  public function exportGrainCategoryWithInventoryToExcel()
  {

    Excel::create('Category Inventory',
function($excel)
{
    $excel->sheet('Categories With Inventory',
        function($sheet)
        {
      $members = MembersGrainInventoryCategory::with('inventory')
      
      ->get();
                            
      $sheet->loadView('reports.inventory.categoriesWithInventory',array('members'=>$members,'title'=>'Categories With Inventory'));
              
        });
})->export('xls');
  }
}
