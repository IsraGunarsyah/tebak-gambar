<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// Wajib tambahkan "implements ShouldBroadcastNow" agar dikirim secara instan
class GameStateChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $state;
    public $data;

    /**
     * Create a new event instance.
     */
    public function __construct($state, $data = [])
    {
        $this->state = $state; // Contoh isi: 'BUZZER_OPEN', 'BUZZER_CLOSED', 'MANUAL_MODE'
        $this->data = $data;   // Contoh isi: ['winner' => 'Budi'] atau ['question_id' => 1]
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        // Pesan disiarkan ke channel publik bernama "quiz-channel"
        return [
            new Channel('quiz-channel'),
        ];
    }
}
