<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ShareReceipt extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
   
    protected $member;
    protected $text;
    protected $receipt;
    public function __construct($member,$text,$receipt)
    {
        //
        $this->member = $member;
        $this->text =$text;
        $this->receipt = $receipt;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

$mailler = $this->view('mail.shareReceipt')->subject('Share Statement')->with(['member'=>$this->member,'text'=>$this->text])->attach($this->receipt);  
   }
}
