<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewUser extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $user;
    protected $password;

    public function __construct($user,$password)
    {
        //
        $this->user =$user;
        $this->password =$password;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
return $this->view('mail.newUser')->with(['user'=>$this->user,'password'=>$this->password]);
          
              }
}
