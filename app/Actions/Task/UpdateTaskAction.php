<?php

namespace App\Actions\Task;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Models\Task;

class UpdateTaskAction
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    /**
     * Execute the action to update a task
     */
    public function execute(Task $task, array $data): Task
    {
        return $this->taskRepository->update($task, $data);
    }
}
