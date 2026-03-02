<?php

namespace App\Events;

use App\Models\Import;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportProgressUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $import;

    public function __construct(Import $import)
    {
        $this->import = $import;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('imports.' . $this->import->user_id);
    }

    public function broadcastAs(): string
    {
        return 'import.progress';
    }

    public function broadcastWith(): array
    {
        $data = [
            'id' => $this->import->id,
            'status' => $this->import->status,
            'total_rows' => $this->import->total_rows,
            'processed_rows' => $this->import->processed_rows,
            'failed_rows' => $this->import->failed_rows,
            'progress_percentage' => $this->import->getProgressPercentage(),
        ];

        return $data;
    }
}
