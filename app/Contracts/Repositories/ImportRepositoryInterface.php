<?php

namespace App\Contracts\Repositories;

use App\Models\Import;
use Illuminate\Database\Eloquent\Collection;

interface ImportRepositoryInterface extends RepositoryInterface
{
    /**
     * Create an import for a user
     */
    public function createForUser(int $userId, array $data): Import;

    /**
     * Get all imports for a user ordered by created date
     */
    public function getImportsForUser(int $userId): Collection;
}
