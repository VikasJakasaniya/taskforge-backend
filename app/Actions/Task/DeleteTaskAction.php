<?php

namespace App\Actions\Task;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Models\Task;

class DeleteTaskAction
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    /**
     * Execute the action to delete a task
     */
    public function execute(Task $task): bool
    {
        return $this->taskRepository->delete($task);
    }
}
