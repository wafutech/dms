<?php

namespace App\Listeners;

use App\Events\PaymentsReceivedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use DB;
use PDF;
use App\Member;
use App\Share;
use App\ReceiptDownload;
use Auth;

class PreparePaymentReceiptListener
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
     * @param  PaymentsReceivedEvent  $event
     * @return void
     */
    public function handle(PaymentsReceivedEvent $event)
    {
        $member = Member::find($event->member_number)->toArray();
       
        //spell share amount into words
$f = new \NumberFormatter( locale_get_default(), \NumberFormatter::SPELLOUT );
$f->setTextAttribute(\NumberFormatter::DEFAULT_RULESET, "%spellout-numbering-verbose");
$amount_in_words = ucfirst($f->format($event->amount));

//Process a pdf share payment receipt
$pdf = new Pdf('C:\wkhtmltopdf\wkhtmltopdf.exe');
$pdf=PDF::loadView('docs.registrationReceipt',
    array('name'=>$member['name'],'reptno'=>$event->receipt_no,
        'refno'=>$event->refno,'membershipnumber'=>$event->member_number,
        'amount_in_words'=>$amount_in_words,'amount'=>$event->amount,
        'detail'=>$event->detail,'username'=>Auth::user()->name,'total'=>$event->total,
        'date'=>date('d-m-Y')))->setOption('page-size','A5')->setOption('orientation','Landscape');
//$time = date('His').date('dmY');
  //  $filename = $member_name.$time."-".$member_number;
    $pdf->save('docs/receipts/shares/'.$event->filename.'.pdf');

echo date('H:i:s'), ' Generating your receipt...';

//Save cash receipts details for later download
$receipt_download_path = 'docs/receipts/shares/'.$event->filename.'.pdf';
$receipt = New ReceiptDownload;
$receipt->member_number = $event->member_number;
//$receipt->user_id = Auth::user()->id;
$receipt->download_path = $receipt_download_path;
$receipt->refno = $event->refno;
$receipt->save();
echo date('H:i:s'), ' Payment completed and receipt saved...';
    }
}
