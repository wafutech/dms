<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Pagination\Paginator;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\View\Middleware\ErrorBinder;
use Session;
use App\County;
use App\Member;
use App\Shareholdernextofkin;
use App\Http\CustomClass\MembershipNumberGenerator;
use Carbon\Carbon;
use Validator;
use DB;
use App\Certificate;
use App\ReceiptDownload;
use App\Constituency;
use App\EducationLevel;
use App\Ward;
use PDF;
use App\Skill;
use Auth;
use App\User;
use App\Mail\NewMember;
use Mail;





class MembersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    var $education_level;
    
    var $counties;
    var $constituencies;
    var $wards;

     public function __construct() 
    {
    $this->middleware('auth');
    $this->middleware('role:admin');
   
   /*$this->middleware('role:admin');
    $this->middleware('role:auditor');
    $this->middleware('role:staff'); */
    $this->education_level = EducationLevel::pluck('level','id');
    $this->counties =  County::pluck('county_name','id');
    $this->constituencies = Constituency::where('county_id',42)->pluck('const_name','id');
    $this->wards = Ward::pluck('ward','id');
    

      }

    public function index()
    {
        //

        //$members = Member::paginate(50);
        $members = DB::table('members')
                    ->leftjoin('counties','county_id','counties.id','members.couty_id')
                    ->leftjoin('constituencies','constituency_id','constituencies.id','members.constituency_id')
                    ->leftjoin('wards','ward_id','wards.id','members.wards.id')
                    ->paginate(50);
        //Fetch deleted members
        $deleted_members= Member::onlyTrashed()->get();
        //Fetch members with incomplete credentials

        $incomplete_members = DB::table('members')->where('idnumber',null)->orWhere('phone_contact',null)->orWhere('year_of_birth',null)->orWhere('name',null)->get(); 


        return view('members/index',array('title'=>'Members','members'=>$members,'deleted_members'=>$deleted_members,'incomplete'=>$incomplete_members));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        

        return view('members.create',array('education_level'=>$this->education_level,
            'counties'=>$this->counties,'title'=>'Add New member'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Get membership number
        $membershipnumber= New MembershipNumberGenerator;
        $membershipnumber= $membershipnumber->MembershipNumber();

        if(Member::where('member_registration_number',$membershipnumber)
        ->count()>0)
        {
        $membershipnumber= New MembershipNumberGenerator;
        $membershipnumber= $membershipnumber->MembershipNumber();

        }

        //Validate form input
        $validation_rules = array(
          'name'         => 'bail|required|string',
          'year_of_birth'         => 'required|date|before:today',
          'sex'      => 'required|alpha',        
          //'landsize'                 => 'required|numeric',
          'idnumber'                  => 'required|numeric|unique:members|digits_between:6,8',
          'phone_contact'                => 'required|numeric|digits:10|unique:members',
          'email'      => 'email|unique:members',
          'town'         => 'alpha',
          //'postal_code'         => 'numeric',
          //'phisical_address'         => 'required',
          'county'      => 'required',        
          'sub_county'                 => 'required|string',
          'constituency' =>'required',
          'ward'                  => 'required|numeric',
          'education_level'                => 'required',
         // 'occupation'      => 'required',
          'registration_fee'                => 'required|numeric',
          //'receipt_no'      => 'required|numeric|unique:members',
          'registration_date'      => 'required|date|before:tomorrow',
          'terms' => 'required|accepted',
          
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
     if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      } 
      //Dynamically generate member age
      $dob =$request->input('year_of_birth');
      $dob = Carbon::parse($dob);
      $age =    Carbon::parse($dob)->age;

      //Validate for minor registration
      if($age<18)
      {
        $error = "Sorry! only adults of 18 years or above can be be members";
    return  Redirect::back()->withErrors($error)->withInput();

      }
      
      $rptno = DB::table('members')->orderBy('id','desc')->first()->receipt_no;
     
    
   
    $receipt_no = $rptno+1;

     
        //save in the database
        $member = new Member;
        $member->name=$request->input('name');
        $member->year_of_birth= $request->input('year_of_birth');
        $member->sex=$request->input('sex');
        $member->landsize=$request->input('landsize');
        $member->idnumber=$request->input('idnumber');
        $member->phone_contact=$request->input('phone_contact');
        $member->email=$request->input('email');
        $member->postal_address=$request->input('postal_address');
        $member->town=$request->input('town');
        $member->town=$request->input('postal_code');
        $member->phisical_address=$request->input('phisical_address');
        $member->county_id=$request->input('county');
        $member->constituency_id=$request->input('constituency');
        $member->sub_county=$request->input('sub_county');
        $member->ward_id=$request->input('ward');
        $member->education_level=$request->input('education_level');
        $member->occupation=$request->input('occupation');
        $member->registration_fee=$request->input('registration_fee');
        $member->receipt_no=$receipt_no;
        $member->member_registration_number=$membershipnumber;
        $member->registration_date= $request->input('registration_date');
        $member->user_id= Auth::user()->id;
        $member->save();  

        //insert skills into skills table
        $skills =explode(',', Input::get('skills'));
        for($i=0;$i<count($skills);$i++)
        {
          $skill = new Skill;
          $skill->member_number = $member->member_registration_number;
          $skill->skill = $skills[$i];
          $skill->save(); 

                           
        }


        //Prepare registration certificate to be downloaded
      $fc = $member->name[0];
      $sortcode = substr($member->member_registration_number, -2)*2;

$sn = $fc.$sortcode.$member->idnumber.$member->member_number;
        $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');
$pdf = PDF::loadView('docs.certificate', array('name'=>$member->name,
      'MembershipNumber'=>$member->member_registration_number,'idnumber'=>$member->idnumber,
      'serial'=>$sn,'regdate'=>date('d-m-Y')));

/* Save certificate of registration -File path*/
$filename = $member->name."-".$member->member_registration_number;
$path = 'docs/registrationCerts/'.$filename.'.pdf';
 $pdf->save('docs/registrationCerts/'.$filename.'.pdf');


//save certificate details in the database
$cert = New Certificate;
$cert->member_number = $member->member_registration_number;
$cert->download_path = $path;
$cert->serial =$sn;
$cert->save();
//Process cash receipt for registration fee

//Calculate total shares paid in by the requested member id
                $total = DB::table('members')
                           ->select(DB::raw('member_registration_number'),DB::raw('sum(registration_fee) as total'))
                            ->where('member_registration_number','=',$member->member_registration_number)
                            ->groupBy(DB::raw('member_registration_number'))
                            ->first();

$total = number_format(Input::get('registration_fee'),2);
$detail='Membership registration fee';
$refno = rand(2302,654322);
//spell share amount in words
$f = new \NumberFormatter( locale_get_default(), \NumberFormatter::SPELLOUT );
$f->setTextAttribute(\NumberFormatter::DEFAULT_RULESET, "%spellout-numbering-verbose");
$amount_in_words = ucfirst($f->format(Input::get('registration_fee')));
//Process registration receipt and save it in PDF format        

echo date('H:i:s'), ' Generating your receipt...';
$pdf=PDF::loadView('docs.registrationReceipt',
    array('name'=>$member->name,'reptno'=>$member->receipt_no,
        'refno'=>$refno,'membershipnumber'=>$member->member_registration_number,
        'amount_in_words'=>$amount_in_words,'amount'=>$member->registration_fee,
        'detail'=>$detail,'username'=>Auth::user()->name,'total'=>$total,
        'date'=>date('d-m-Y')))->setOption('page-size','A5')->setOption('orientation','Landscape');
$time = date('His').date('dmY');
    $filename = $member->name.$time."-".$member->member_registration_number;
    $pdf->save('docs/receipts/registration/'.$filename.'.pdf');


echo date('H:i:s'), ' Payment completed and receipt saved...';

//Save cash payment details for later download
$receipt_download_path = 'docs/receipts/registration/'.$filename.'.pdf';
$receipt = New ReceiptDownload;
$receipt->member_number = $member->member_registration_number;
$receipt->download_path = $receipt_download_path;
$receipt->refno = $refno;
$receipt->save();
//Tear off the receipt from the receipt book
 DB::update('update receipt_numbers set used=? where number =?',[1,$member->receipt_no]);

 //Email new member registration certificate
 if($member->email!=null)
 {
  $to = $member->email;
      $member = Member::where('member_registration_number',$member->member_registration_number)->first();
      $regCert = $cert->download_path;
      $paymentReceipt = $receipt->download_path;

      Mail::to($to)->send(new NewMember($member,$regCert,$paymentReceipt));
 }

//send feedback to the user

 $message = "New member successfully added. The registration number for".
        " ". $member->name."  is ".$membershipnumber." . 
        please record this number and keep it safely. You will not make any transaction ralating 
        to shareholders without first providing it." ;

        Session::flash('message', $message);
       return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $member= Member::findOrFail($id);

        $member = DB::table('members')
                    ->leftjoin('counties','county_id','counties.id','members.county_id')
                    ->leftjoin('constituencies','constituency_id','constituencies.id','members.constituency_id')
                    ->leftjoin('wards','ward_id','wards.id','members.wards.id')
                    ->leftjoin('education_levels','education_level','education_levels.id','members.education_level')
                    ->where('members.id',$member->id)
                    ->first();

        //check if the member has a Next of kin
        $nextOfKin = DB::table('shareholder_nextofkins')
                ->where('member_number',$member->member_registration_number)
                ->first();


        if(count($nextOfKin)!=0)
        {
            $has_nextOfKin =1;
        }
        else
        {
            $has_nextOfKin =0;
            Session::put('member_number',$member->member_registration_number);

        }
        return view('members.show',array('member'=>$member,'has_nextOfKin'=>$has_nextOfKin));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $member= Member::findOrFail($id);
        $constituencies = Constituency::where('county_id',42)->pluck('const_name','id');
       $wards = Ward::pluck('ward','id');
       $skills = Skill::where('member_number',$id)->get();
        return view('members.edit',array('member'=>$member,'title'=>'Edit Member Credentials',
           'counties'=> $this->counties,'education_level'=>$this->education_level,'constituencies'=>$constituencies,'wards'=>$wards,'skills'=>$skills));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        //$member = Member::where('member_registration_number',$id)->first();
        $member = Member::findOrFail($id);
        
          $input = $request->all();
          $member->fill($input)->save();
        
        
        Session::flash('message', 'Member successfully updated!'); 
      
         

    return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $member = Member::findOrFail($id);
       $member->delete();

   
    Session::flash('message', 'Member successfully deleted!');

    $message = 'Member successfully deleted!';
    return redirect()->route('members.index')->with('message',$message);
    //return redirect()->route('members.destroy');
    }
        
        
}
