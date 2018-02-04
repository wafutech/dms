<?php

namespace App\Listeners;

use App\Events\ImportMembersEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Auth;
use DB;
use App\Member;
use App\Certificate;
use App\ReceiptDownload;
use PDF;
use Storage;

class CreateRegisrationCertificateToImportedMembers
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
     * @param  ImportMembersEvent  $event
     * @return void
     */
    public function handle(ImportMembersEvent $event)
    {
        //
       $members= Member::where('imported',1)->get();

       foreach($members as $member) 
       {

        $fc = $member->name[0];
      $sortcode = substr($member->member_registration_number, -2)*2;
    if($member->idnumber==null)
    {
        $member->idnumber= rand(1234567,34567899);
    }

$sn = $fc.$sortcode.$member->idnumber.$member->member_number;
        $pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');
$pdf = PDF::loadView('docs.certificate', array('name'=>$member->name,
      'MembershipNumber'=>$member->member_registration_number,'idnumber'=>$member->idnumber,
      'serial'=>$sn,'regdate'=>date('d-m-Y')));

/* Save certificate of registration -File path*/
$filename = $member->name."-".$member->member_registration_number;
$path = 'docs/registrationCerts/'.$filename.'.pdf';
if (file_exists($path)) {
       Storage::delete($filename);
    }
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
        'date'=>date('d-m-Y')));
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

//Updated update field to 1 so that to prevent further generation of the registration certificate for the same members
DB::update('update members set imported=? where member_registration_number =?',[0,$member->member_registration_number]);
//Tear off the receipt from the receipt book
 DB::update('update receipt_numbers set used=? where number =?',[1,$member->receipt_no]); 
       }    
        
        
    }
}
