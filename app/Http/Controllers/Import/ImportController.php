<?php

namespace App\Http\Controllers\Import;

use App\Actions\Import\CreateImportAction;
use App\Actions\Import\DeleteImportAction;
use App\Actions\Import\GetImportsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Import\UploadImportRequest;
use App\Http\Resources\ImportResource;
use App\Models\Import;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    public function __construct(
        private GetImportsAction $getImportsAction,
        private CreateImportAction $createImportAction,
        private DeleteImportAction $deleteImportAction
    ) {}

    /**
     * Get all imports for authenticated user
     */
    public function index(): JsonResponse
    {
        $imports = $this->getImportsAction->execute(auth()->id());

        return response()->json([
            'data' => ImportResource::collection($imports)
        ]);
    }

    /**
     * Upload and process CSV import
     */
    public function store(UploadImportRequest $request): JsonResponse
    {
        try {
            $import = $this->createImportAction->execute(
                auth()->id(),
                $request->file('file')
            );

            return response()->json([
                'message' => 'Import started successfully',
                'data' => new ImportResource($import)
            ], 201);

        } catch (\Exception $e) {
            Log::error('Import creation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to process import: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single import details
     */
    public function show(Import $import): JsonResponse
    {
        Gate::authorize('view', $import);

        return response()->json([
            'data' => new ImportResource($import)
        ]);
    }

    /**
     * Delete an import record
     */
    public function destroy(Import $import): JsonResponse
    {
        Gate::authorize('delete', $import);

        $this->deleteImportAction->execute($import);

        return response()->json([
            'message' => 'Import deleted successfully'
        ]);
    }
}
