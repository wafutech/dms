<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewMember extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    protected $member;
    protected $regCert;
    protected $paymentReceipt;
    public function __construct($member,$regCert,$paymentReceipt)
    {
        //
         $this->member = $member;
        $this->regCert = $regCert;
        $this->paymentReceipt = $paymentReceipt;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $attachments = [$this->regCert,$this->paymentReceipt];
  $mailler = $this->view('mail.newMember')->subject('Your Member Registration Information')->with(['member'=>$this->member]);
  foreach ($attachments as $attachment) {
    $mailler->attach($attachment);
  }
          return $mailler;    
    
      }
}
