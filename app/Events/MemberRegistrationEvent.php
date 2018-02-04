<?php
namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MemberRegistrationEvent
{
    use InteractsWithSockets, SerializesModels;
    public $memberid;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($memberid)
    {
        //
        $this->memberid = $memberid;
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