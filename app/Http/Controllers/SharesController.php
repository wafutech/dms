<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use App\Http\Controllers\Auth;
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
use App\Mail\ShareReceipt;
use Mail;


class SharesController extends Controller
{

     public function __construct() 
    {
$this->middleware(['auth']);
$this->middleware('role:admin');




     } 
    

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $shares = DB::table('shares')->leftjoin('users','user_id','users.id','shares.user_id')
        ->select(DB::raw('shares.id'),DB::raw('shares.amount'),DB::raw('shares.member_number'),DB::raw('shares.receipt_no'),DB::raw('shares.date_paid'),DB::raw('users.name'))
        ->orderBy('shares.member_number','asc')
        ->paginate(50);
        

        return view('shares.index',array('title'=>'shares','shares'=>$shares));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //

return view('shares.create',array('title'=>'Receive Shares'));
    
   
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $validation_rules = array(
          'member_number'         => 'bail|required|numeric|exists:members,member_registration_number',
          'amount'         => 'required|numeric',
          //'receipt_no'      => 'required|numeric|unique:shares|exists:receipt_numbers,number', 
          'date_paid'      => 'required|date',       
          
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
      if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      } 
      //validate user request to ensure that the member identification
      //is associated with a member in the member's register
     $amount = number_format(Input::get('amount'),2);
     
     
      //receipt fetch next receipt number
    $rptno = DB::table('shares')->latest()->first()->receipt_no;
    
    //$rptno =6113;
   
    $receipt_no = $rptno+1;
    
   
    
    //spell share amount into words
    
$f = new \NumberFormatter( locale_get_default(), \NumberFormatter::SPELLOUT );
$f->setTextAttribute(\NumberFormatter::DEFAULT_RULESET, "%spellout-numbering-verbose");
$amount_in_words = ucfirst($f->format(Input::get('amount')));

     //$receipt_no = Input::get('receipt_no');
     
   DB::update('update receipt_numbers set used=? where number =?',[1,$receipt_no]);         
            
          

     $refno = rand(2302,654322);

      $is_member = DB::table('members')
                    ->where('member_registration_number',$request->input('member_number'))
                    ->first();
                if($is_member !=Null)
                {
                    $member_name= ucfirst($is_member->name);
                    $id_number = $is_member->idnumber;
                    //Go ahead with the request and save since
                    //the member exists
            $share = new Share;
            $share->member_number = $request->input('member_number');
            $share->amount = $request->input('amount');
            $share->receipt_no = $receipt_no;
            $share->user_id = Auth::User()->id;
            $share->date_paid = $request->input('date_paid');
            $share->imported =0;
            $share->save();
            
//$shares = Input::all();
       // $newshare =  Share::create($shares);
        //prepare share receipt
        //$phpWord = new \PhpOffice\PhpWord\PhpWord();


//$receipt_no = Input::get('receipt_no');
$member_number = Input::get('member_number');
$time=date('H:i:s');
$filename = $member_name.$receipt_no.$time."_".$member_number;

//Calculate total shares paid in by the requested member id
                $total = DB::table('shares')
                           ->select(DB::raw('member_number'),DB::raw('sum(amount) as total'))
                            ->where('member_number','=',$member_number)
                            ->groupBy(DB::raw('member_number'))
                            ->first();
                                                      
                    $total = number_format($total->total,2);


// Template processor instance creation

$detail='Being share investment in Nzoia Grain and marketing Cooperative society';
//Process a pdf share payment receipt
$pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');
$pdf=PDF::loadView('docs.registrationReceipt',
    array('name'=>$member_name,'reptno'=>$receipt_no,
        'refno'=>$refno,'membershipnumber'=>$member_number,
        'amount_in_words'=>$amount_in_words,'amount'=>$amount,
        'detail'=>$detail,'username'=>Auth::user()->name,'total'=>$total,
        'date'=>date('d-m-Y')))->setOption('page-size','A5')->setOption('orientation','Landscape');
$time = date('His').date('dmY');
    $filename = $member_name.$time."-".$member_number;
    $pdf->save('docs/receipts/shares/'.$filename.'.pdf');

echo date('H:i:s'), ' Generating your receipt...';

//Save cash receipts details for later download
$receipt_download_path = 'docs/receipts/shares/'.$filename.'.pdf';




$receipt = New ReceiptDownload;
$receipt->member_number = $member_number;
//$receipt->user_id = Auth::user()->id;
$receipt->download_path = $receipt_download_path;
$receipt->refno = $refno;
$receipt->save();

//Email PDF version of the receipt to the shareholder
//First fetch the member information
      $member = Member::where('member_registration_number',$member_number)->first();

 if($member->email!=null)
 {
  $to = $member->email;

      $receipt = $receipt_download_path;
      $text = "The share amount of ".$request->input('amount')." has been credited on  your share account "." You were served by: ".Auth::User()->name;


      Mail::to($to)->send(new ShareReceipt($member,$text,$receipt));
     
 }

echo date('H:i:s'), ' Payment completed and receipt saved...';
        
//prepare user message for successful request
$message = "The share amount of ".$request->input('amount')." has been credited on share account of ".
 $member_name.", national ID #".$id_number." and account# ".$request->input('member_number');       
Session::flash('message', $message);
return redirect()->back();
   }

   //Else reject the request
   $message = "Your request was rejected due to the following reason: Member identification number ".
   $request->input('member_number')." does NOT exist";
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
        $share= Share::findOrFail($id);
        return view('shares.show',array('share'=>$share));
        
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
         $share= Share::findOrFail($id);
        return view('shares.edit',array('share'=>$share,'title'=>'Edit member share'));
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
        //
        $share = Share::findOrFail($id);
          $input = $request->all();
         $share->fill($input)->save();
            Session::flash('message', 'Share successfully updated!');

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
    DB::table('shares')->where('id', $id)->delete();

       /* $share = Share::find($id);
        dd($share);
    $share->delete();*/
  
    return redirect()->back()->with('message','The share entry has been permanently deleted!');
    }
}
