<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class testMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $member;
    protected $regCert;


    public function __construct($member,$regCert)
    {
        //
        $this->member = $member;
        $this->regCert = $regCert;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.newMember')->with(['member'=>$this->member])
          ->attach($this->regCert->download_path);
    }
}
