<?php

namespace App\Jobs;

use App\Models\Import;
use App\Models\Task;
use App\Services\ImportService;
use App\Events\ImportProgressUpdated;
use App\Events\ImportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessImportChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $importId,
        public array $chunk,
        public int $chunkIndex
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ImportService $importService): void
    {
        $import = Import::find($this->importId);

        if (!$import) {
            Log::error("Import not found: {$this->importId}");
            return;
        }

        // Mark as processing on first chunk
        if ($import->status === 'queued') {
            $import->markAsProcessing();
        }

        // Demo mode delay
        if (config('app.import_demo_mode', false)) {
            sleep(config('app.import_demo_delay', 1));
        }

        $processedCount = 0;
        $failedCount = 0;

        foreach ($this->chunk as $index => $row) {
            try {
                // Validate row
                $errors = $importService->validateTaskRow($row);

                if (!empty($errors)) {
                    Log::info("Error in validation: ", $errors);
                    $failedCount++;
                    $import->addFailedRowDetails([
                        'row' => $this->chunkIndex * count($this->chunk) + $index + 2, // +2 for header and 1-indexed
                        'data' => $row,
                        'errors' => $errors,
                    ]);
                    continue;
                }

                // Normalize and create task
                $taskData = $importService->normalizeTaskData($row);
                $taskData['user_id'] = $import->user_id;

                Task::create($taskData);
                $processedCount++;

            } catch (\Exception $e) {
                $failedCount++;
                Log::error("Failed to process row in import {$this->importId}: " . $e->getMessage(), [
                    'row' => $row,
                    'chunk_index' => $this->chunkIndex,
                ]);

                $import->addFailedRowDetails([
                    'row' => $this->chunkIndex * count($this->chunk) + $index + 2,
                    'data' => $row,
                    'errors' => [$e->getMessage()],
                ]);
            }
        }

        // Update progress atomically
        DB::transaction(function () use ($import, $processedCount, $failedCount) {
            $import->incrementProcessed($processedCount);
            $import->incrementFailed($failedCount);
            $import->refresh();
        });

        // Broadcast progress
        broadcast(new ImportProgressUpdated($import));

        // Check if import is complete
        if ($import->processed_rows + $import->failed_rows >= $import->total_rows) {
            $import->markAsCompleted();
            broadcast(new ImportCompleted($import));
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $import = Import::find($this->importId);

        if ($import) {
            $import->markAsFailed($exception->getMessage());
            Log::error("Import job failed: {$this->importId}", [
                'exception' => $exception->getMessage(),
                'chunk_index' => $this->chunkIndex,
            ]);
        }
    }
}
