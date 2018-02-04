<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ShareStatement extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    protected $member;
    protected $statement;
    protected $filename;
    public function __construct($member,$statement,$filename)
    {
        //
        $this->member = $member;
        $this->statement = $statement;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->filename = $this->filename.'.pdf';
  $mailler = $this->view('mail.shareStatement')->subject('Share Statment')->with(['member'=>$this->member])->attachData($this->statement,$this->filename,['mime' => 'application/pdf',]); 


           }
}
