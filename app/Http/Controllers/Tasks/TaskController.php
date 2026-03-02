<?php

namespace App\Http\Controllers\Tasks;

use App\Actions\Task\CreateTaskAction;
use App\Actions\Task\DeleteTaskAction;
use App\Actions\Task\GetTasksAction;
use App\Actions\Task\UpdateTaskAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\TaskFilterRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function __construct(
        private GetTasksAction $getTasksAction,
        private CreateTaskAction $createTaskAction,
        private UpdateTaskAction $updateTaskAction,
        private DeleteTaskAction $deleteTaskAction
    ) {}

    /**
     * Get paginated tasks with filters
     */
    public function index(TaskFilterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 15;

        $tasks = $this->getTasksAction->execute(
            auth()->id(),
            $validated,
            $perPage
        );

        return response()->json([
            'data' => TaskResource::collection($tasks->items()),
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ]
        ]);
    }

    /**
     * Store a new task
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->createTaskAction->execute(
            auth()->id(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Task created successfully',
            'data' => new TaskResource($task)
        ], 201);
    }

    /**
     * Get a single task
     */
    public function show(Task $task): JsonResponse
    {
        Gate::authorize('view', $task);

        return response()->json([
            'data' => new TaskResource($task)
        ]);
    }

    /**
     * Update a task
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        Gate::authorize('update', $task);

        $updatedTask = $this->updateTaskAction->execute($task, $request->validated());

        return response()->json([
            'message' => 'Task updated successfully',
            'data' => new TaskResource($updatedTask)
        ]);
    }

    /**
     * Delete a task
     */
    public function destroy(Task $task): JsonResponse
    {
        Gate::authorize('delete', $task);

        $this->deleteTaskAction->execute($task);

        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }
}
