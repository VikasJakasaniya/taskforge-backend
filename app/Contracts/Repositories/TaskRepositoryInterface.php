<?php

namespace App\Contracts\Repositories;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface extends RepositoryInterface
{
    /**
     * Get tasks for a specific user with filters
     */
    public function getTasksForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all tasks for a specific user
     */
    public function getAllTasksForUser(int $userId): Collection;

    /**
     * Create a task for a user
     */
    public function createForUser(int $userId, array $data): Task;
}
