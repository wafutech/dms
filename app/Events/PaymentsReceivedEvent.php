<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PaymentsReceivedEvent
{
    use InteractsWithSockets, SerializesModels;
    public $member_number;
    public $detail;
    public $filename;
    public $refno;
    public $total;
    public $amount;
    public $receipt_no;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($member_number,$amount,$receipt_no,$detail,$filename,$refno,$total)
    {
        //
        $this->member_number = $member_number;
        $this->amount =$amount;
        $this->detail =$detail;
        $this->filename =$filename;
        $this->refno =$refno;
        $this->total =$total;
        $this->receipt_no = $receipt_no;
    }
  

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
