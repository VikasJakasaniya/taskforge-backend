<?php

namespace Tests\Feature\Task;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskAdvancedTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_user_can_filter_tasks_by_priority(): void
    {
        Task::factory()->create(['user_id' => $this->user->id, 'priority' => 'high']);
        Task::factory()->create(['user_id' => $this->user->id, 'priority' => 'low']);
        Task::factory()->create(['user_id' => $this->user->id, 'priority' => 'medium']);

        $response = $this->getJson('/api/tasks?priority=high');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.priority', 'high');
    }

    public function test_user_can_filter_by_date_range(): void
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => '2024-01-15'
        ]);
        Task::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => '2024-02-15'
        ]);
        Task::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => '2024-03-15'
        ]);

        $response = $this->getJson('/api/tasks?due_date_from=2024-02-01&due_date_to=2024-02-28');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_search_tasks(): void
    {
        Task::factory()->create(['user_id' => $this->user->id, 'title' => 'Important Meeting']);
        Task::factory()->create(['user_id' => $this->user->id, 'title' => 'Buy Groceries']);

        $response = $this->getJson('/api/tasks?search=Meeting');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Important Meeting');
    }

    public function test_user_can_sort_tasks(): void
    {
        Task::factory()->create(['user_id' => $this->user->id, 'title' => 'C Task']);
        Task::factory()->create(['user_id' => $this->user->id, 'title' => 'A Task']);
        Task::factory()->create(['user_id' => $this->user->id, 'title' => 'B Task']);

        $response = $this->getJson('/api/tasks?sort_by=title&sort_direction=asc');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals('A Task', $data[0]['title']);
        $this->assertEquals('B Task', $data[1]['title']);
        $this->assertEquals('C Task', $data[2]['title']);
    }

    public function test_pagination_works_correctly(): void
    {
        Task::factory()->count(25)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/tasks?per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 25);
    }

    public function test_validation_fails_for_invalid_task_data(): void
    {
        $response = $this->postJson('/api/tasks', [
            // Missing required 'title' field
            'status' => 'invalid_status',
            'priority' => 'invalid_priority'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'status', 'priority']);
    }

    public function test_task_validation_passes_for_valid_data(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Valid Task',
            'description' => 'Valid Description',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => '2024-12-31'
        ]);

        $response->assertStatus(201);
    }

    public function test_user_can_view_single_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $task->id)
            ->assertJsonPath('data.title', $task->title);
    }

    public function test_unauthenticated_user_cannot_access_tasks(): void
    {
        Sanctum::actingAs(null);

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(401);
    }

    public function test_task_resource_has_correct_structure(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'priority',
                    'due_date',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }
}
