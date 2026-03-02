<?php

namespace App\Services;

use App\Models\Import;
use App\Models\User;
use App\Jobs\ProcessImportChunk;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ImportService
{
    /**
     * Create import and dispatch jobs
     */
    public function createImport(User $user, UploadedFile $file): Import
    {
        // Store file
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('imports', $filename, 'local');

        // Parse CSV to get total rows
        $csvData = $this->parseCsv(Storage::path($path));
        $totalRows = count($csvData);

        // Create import record
        $import = Import::create([
            'user_id' => $user->id,
            'filename' => $filename,
            'status' => 'queued',
            'total_rows' => $totalRows,
        ]);

        // Dispatch chunked jobs
        $this->dispatchChunkedJobs($import, $csvData);

        return $import;
    }

    /**
     * Parse CSV file
     */
    private function parseCsv(string $filePath): array
    {
        $data = [];
        $header = null;

        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                if (!$header) {
                    $header = $row;
                    continue;
                }

                // Map CSV columns to array
                if (count($header) === count($row)) {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }

    /**
     * Dispatch chunked jobs
     */
    private function dispatchChunkedJobs(Import $import, array $data): void
    {
        $chunkSize = config('app.import_chunk_size', 500);
        $chunks = array_chunk($data, $chunkSize);

        foreach ($chunks as $index => $chunk) {
            ProcessImportChunk::dispatch($import->id, $chunk, $index)
                ->onQueue('import');
        }
    }

    /**
     * Validate CSV row for task creation
     */
    public function validateTaskRow(array $row): array
    {
        $errors = [];

        // Required: title
        if (empty($row['title'])) {
            $errors[] = 'Title is required';
        }

        // Validate status
        if (!empty($row['status']) && !in_array($row['status'], ['pending', 'in_progress', 'done'])) {
            $errors[] = 'Invalid status. Must be: pending, in_progress, or done';
        }

        // Validate priority
        if (!empty($row['priority']) && !in_array($row['priority'], ['low', 'medium', 'high'])) {
            $errors[] = 'Invalid priority. Must be: low, medium, or high';
        }

        // Validate due_date format
        if (!empty($row['due_date'])) {
            $date = \DateTime::createFromFormat('Y-m-d', $row['due_date']);
            if (!$date || $date->format('Y-m-d') !== $row['due_date']) {
                $errors[] = 'Invalid due_date format. Expected: Y-m-d';
            }
        }

        return $errors;
    }

    /**
     * Normalize task data from CSV row
     */
    public function normalizeTaskData(array $row): array
    {
        return [
            'title' => $row['title'] ?? '',
            'description' => $row['description'] ?? null,
            'status' => $row['status'] ?? 'pending',
            'priority' => $row['priority'] ?? 'medium',
            'due_date' => !empty($row['due_date']) ? $row['due_date'] : null,
        ];
    }
}
