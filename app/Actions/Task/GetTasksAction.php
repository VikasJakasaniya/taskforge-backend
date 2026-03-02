<?php

namespace App\Actions\Task;

use App\Contracts\Repositories\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetTasksAction
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    /**
     * Execute the action to get tasks with filters
     */
    public function execute(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->getTasksForUser($userId, $filters, $perPage);
    }
}
