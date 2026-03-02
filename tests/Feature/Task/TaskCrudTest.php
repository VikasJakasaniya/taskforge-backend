<?php

namespace Tests\Feature\Task;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_user_can_create_task(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => '2024-12-31',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'title', 'status', 'priority'],
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_list_their_tasks(): void
    {
        $this->actingAs($this->user); // 🔐 authenticate

        // Create 3 tasks for logged-in user
        Task::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        // Create tasks for another user
        $otherUser = User::factory()->create();

        Task::factory()->count(2)->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_filter_tasks_by_status(): void
    {
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'done']);

        $response = $this->getJson('/api/tasks?status=pending');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['status' => 'pending']
                ]
            ]);
    }

    public function test_user_can_update_their_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->patchJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Title',
            'status' => 'done',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'status' => 'done',
        ]);
    }

    public function test_user_cannot_update_other_users_task(): void
    {
        $otherUser = User::factory()->create();

        $otherUsersTask = Task::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/tasks/{$otherUsersTask->id}", [
                'title' => 'Hacked Title',
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_their_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
