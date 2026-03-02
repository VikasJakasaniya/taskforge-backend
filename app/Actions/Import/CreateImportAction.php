<?php

namespace App\Actions\Import;

use App\Contracts\Repositories\ImportRepositoryInterface;
use App\Jobs\ProcessImportChunk;
use App\Models\Import;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CreateImportAction
{
    public function __construct(
        private ImportRepositoryInterface $importRepository
    ) {}

    /**
     * Execute the action to create an import
     */
    public function execute(int $userId, UploadedFile $file): Import
    {
        // Store file
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('imports', $filename, 'local');

        // Parse CSV to get total rows
        $csvData = $this->parseCsv(Storage::path($path));
        $totalRows = count($csvData);

        // Create import record
        $import = $this->importRepository->createForUser($userId, [
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
}
