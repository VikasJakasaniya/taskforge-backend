<?php

namespace App\Actions\Import;

use App\Contracts\Repositories\ImportRepositoryInterface;
use App\Models\Import;

class DeleteImportAction
{
    public function __construct(
        private ImportRepositoryInterface $importRepository
    ) {}

    /**
     * Execute the action to delete an import
     */
    public function execute(Import $import): bool
    {
        return $this->importRepository->delete($import);
    }
}
