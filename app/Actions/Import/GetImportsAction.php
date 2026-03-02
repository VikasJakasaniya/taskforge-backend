<?php

namespace App\Actions\Import;

use App\Contracts\Repositories\ImportRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GetImportsAction
{
    public function __construct(
        private ImportRepositoryInterface $importRepository
    ) {}

    /**
     * Execute the action to get imports for a user
     */
    public function execute(int $userId): Collection
    {
        return $this->importRepository->getImportsForUser($userId);
    }
}
