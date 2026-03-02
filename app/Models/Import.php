<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'status',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'error_message',
        'failed_row_details',
    ];

    protected $casts = [
        'failed_row_details' => 'array',
    ];

    /**
     * Get the user that owns the import.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update progress atomically
     */
    public function incrementProcessed(int $count = 1): void
    {
        $this->increment('processed_rows', $count);
    }

    /**
     * Update failed rows atomically
     */
    public function incrementFailed(int $count = 1): void
    {
        $this->increment('failed_rows', $count);
    }

    /**
     * Mark import as processing
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark import as completed
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Mark import as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Add failed row details
     */
    public function addFailedRowDetails(array $details): void
    {
        $currentDetails = $this->failed_row_details ?? [];
        $currentDetails[] = $details;

        // Keep only last 100 failed rows to avoid huge JSON
        if (count($currentDetails) > 100) {
            $currentDetails = array_slice($currentDetails, -100);
        }

        $this->update(['failed_row_details' => $currentDetails]);
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        return round(($this->processed_rows / $this->total_rows) * 100, 2);
    }
}
