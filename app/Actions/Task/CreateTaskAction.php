<?php

namespace App\Actions\Task;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Models\Task;

class CreateTaskAction
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    /**
     * Execute the action to create a task
     */
    public function execute(int $userId, array $data): Task
    {
        return $this->taskRepository->createForUser($userId, $data);
    }
}
