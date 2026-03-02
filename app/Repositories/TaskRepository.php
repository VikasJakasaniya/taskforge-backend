<?php

namespace App\Repositories;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository extends BaseRepository implements TaskRepositoryInterface
{
    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    public function getTasksForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->where('user_id', $userId);

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->status($filters['status']);
        }

        // Apply priority filter
        if (!empty($filters['priority'])) {
            $query->priority($filters['priority']);
        }

        // Apply due date range filter
        if (!empty($filters['due_date_from']) && !empty($filters['due_date_to'])) {
            $query->dueDateBetween($filters['due_date_from'], $filters['due_date_to']);
        }

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Apply sorting
        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->sort($sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    public function getAllTasksForUser(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->get();
    }

    public function createForUser(int $userId, array $data): Task
    {
        return $this->create([
            'user_id' => $userId,
            ...$data
        ]);
    }
}
