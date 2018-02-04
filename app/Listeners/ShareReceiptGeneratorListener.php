<?php

namespace App\Listeners;

use App\Events\ImportSharesEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use DB;
use PhpOffice\PhpWord\Settings;
use App\ReceiptNumber;
use App\ReceiptDownload;
use PDF;
use Auth;
use App\Share;
use App\Member;

class ShareReceiptGeneratorListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ImportSharesEvent  $event
     * @return void
     */
    public function handle(ImportSharesEvent $event)
    {
        //
        $shares = DB::table('shares')
                    ->join('members','member_number','members.member_registration_number','shares.member_number')
                    ->where('shares.imported',1)
                    ->get();
            foreach($shares as $share)
            {
                $member = Member::where('member_registration_number',$share->member_number)->first();
        $total = DB::table('shares')
                           ->select(DB::raw('member_number'),DB::raw('sum(amount) as total'))
                            ->where('member_number','=',$share->member_number)
                            ->groupBy(DB::raw('member_number'))
                            ->first();
                                                      
                    $total = number_format($total->total,2);

$detail='Membership registration fee';
$refno = rand(2302,654322);
//spell share amount in words
$f = new \NumberFormatter( locale_get_default(), \NumberFormatter::SPELLOUT );
$f->setTextAttribute(\NumberFormatter::DEFAULT_RULESET, "%spellout-numbering-verbose");
$amount_in_words = ucfirst($f->format($share->amount));
//Process registration receipt and save it in PDF format        

echo date('H:i:s'), ' Generating your receipt...';
$pdf=PDF::loadView('docs.registrationReceipt',
    array('name'=>$member->name,'reptno'=>$share->receipt_no,
        'refno'=>$refno,'membershipnumber'=>$share->member_number,
        'amount_in_words'=>$amount_in_words,'amount'=>$share->amount,
        'detail'=>$detail,'username'=>Auth::user()->name,'total'=>$total,
        'date'=>date('d-m-Y')))->setOption('page-size','A5')->setOption('orientation','Landscape');
$time = date('His').date('dmY');
    $filename = $member->name.$time."-".$share->member_number;
    $pdf->save('docs/receipts/shares/'.$filename.'.pdf');


echo date('H:i:s'), ' Payment completed and receipt saved...';

//Save cash payment details for later download
$receipt_download_path = 'docs/receipts/shares/'.$filename.'.pdf';
$receipt = New ReceiptDownload;
$receipt->member_number = $share->member_number;
$receipt->download_path = $receipt_download_path;
$receipt->refno = $refno;
$receipt->save();

DB::update('update shares set imported=? where member_number =?',[0,$share->member_number]);
//Tear receipt from receipt book

    DB::update('update receipt_numbers set used=? where number =?',[1,$share->receipt_no]);  
}
         /*$shares = DB::table('shares')
                    ->join('members','member_number','members.member_registration_number','shares.member_number')
                    ->where('shares.imported',1)
                    ->get();

            foreach($shares as $share)
            {
                //receipt processing logic goes here
                $member_number = $share->member_number;
            $amount = $share->amount;  
            $receipt_no = $share->receipt_no;
                
    //spell share amount into words
$f = new \NumberFormatter( locale_get_default(), \NumberFormatter::SPELLOUT );
$f->setTextAttribute(\NumberFormatter::DEFAULT_RULESET, "%spellout-numbering-verbose");
$amount_in_words = ucfirst($f->format($amount));
$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('docs/receipts/cash_receipt.docx');

     //check if the receipt number exists and is valid
    
                //update the receipt number to be used
                $used = ReceiptNumber::findOrFail($receipt_no);
                $input = ['number'=>$receipt_no,'used'=>1];

               $used->fill($input)->save();  
            
           

     $refno = rand(2302,654322);

      $is_member = DB::table('members')
                    ->where('member_registration_number',$member_number)
                    ->first();
                if($is_member !=Null)
                {
                    $member_name= ucfirst($is_member->name);
                    $id_number = $is_member->idnumber;

    //Go ahead with the request and save since
    //the member exists
        //$shares = Input::all();
        //$newshare =  Share::create($shares);
        
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
        'date'=>date('d-m-Y')));
$time.= date('dmY');
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
//Updated update field to 1 so that to prevent further generation of the registration certificate for the same members
DB::update('update shares set imported=? where member_number =?',[0,$share->member_number]);
echo date('H:i:s'), ' Payment completed and receipt saved...';
        
//prepare user message for successful request
$message = "The share amount of ".$request->input('amount')." has been credited on share account of ".
 $member_name.", national ID #".$id_number." and account# ".$request->input('member_number');       
Session::flash('message', $message);
return redirect()->back();
            }
            return "Error!";
    }*/
}
}
