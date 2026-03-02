<?php

namespace Tests\Feature\Import;

use App\Models\User;
use App\Models\Import;
use App\Jobs\ProcessImportChunk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ImportProgressTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        Storage::fake('local');
    }

    public function test_user_can_upload_csv_for_import(): void
    {
        Queue::fake();

        $csv = "title,description,status,priority,due_date\n";
        $csv .= "Task 1,Description 1,pending,high,2024-12-31\n";
        $csv .= "Task 2,Description 2,done,low,2024-11-30\n";

        $file = UploadedFile::fake()->createWithContent('tasks.csv', $csv);

        $response = $this->postJson('/api/imports', [
            'file' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'filename', 'status', 'total_rows'],
            ]);

        Queue::assertPushed(ProcessImportChunk::class);
    }

    public function test_import_tracks_progress_correctly(): void
    {
        $import = Import::factory()->create([
            'user_id' => $this->user->id,
            'total_rows' => 100,
            'processed_rows' => 50,
            'failed_rows' => 5,
        ]);

        $this->assertEquals(50.0, $import->getProgressPercentage());
    }

    public function test_import_can_be_marked_as_completed(): void
    {
        $import = Import::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'processing',
        ]);

        $import->markAsCompleted();

        $this->assertEquals('completed', $import->fresh()->status);
    }
}
