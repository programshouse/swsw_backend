<?php

namespace App\Events;

use App\Http\Resources\OrderResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateOrder implements ShouldBroadcast, ShouldBeEncrypted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    /**
     * Create a new event instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('kitchen.' . $this->order->kitchen_id),
        ];
    }

    public function broadcastAs()
    {
        return "new-order";
    }

       public function broadcastWith()
    {
        return [
            'order' => (new OrderResource($this->order))->resolve()
        ];
    }
}
