<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // 🚨 PENTING: Gunakan ShouldBroadcastNow, bukan ShouldBroadcast biasa!
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameStateChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $state;
    public $data;

    public function __construct($state, $data = [])
    {
        $this->state = $state;
        $this->data = $data;
    }

    // 1. Tentukan nama channel-nya
    public function broadcastOn()
    {
        return new Channel('quiz-channel');
    }

    // 2. 🚨 PENTING: Tentukan nama event pastinya agar dibaca oleh titik (.) di frontend tadi
    public function broadcastAs()
    {
        return 'GameStateChanged';
    }
}
