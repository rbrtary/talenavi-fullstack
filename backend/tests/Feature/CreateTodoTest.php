<?php

namespace Tests\Feature;

use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CreateTodoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-11-18 10:00:00'));
    }

    public function test_it_creates_a_todo_and_returns_the_created_object(): void
    {
        $response = $this->postJson('/api/todos', [
            'title' => 'Build Excel report export API',
            'assignee' => 'John',
            'due_date' => '2026-11-25',
            'time_tracked' => 2.5,
            'status' => 'in_progress',
            'priority' => 'high',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'title', 'assignee', 'due_date', 'time_tracked', 'status', 'priority', 'created_at', 'updated_at'],
            ])
            ->assertJsonPath('message', 'Todo created successfully.')
            ->assertJsonPath('data.title', 'Build Excel report export API')
            ->assertJsonPath('data.assignee', 'John')
            ->assertJsonPath('data.due_date', '2026-11-25')
            ->assertJsonPath('data.time_tracked', 2.5)
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.priority', 'high');

        $this->assertDatabaseHas('todos', [
            'title' => 'Build Excel report export API',
            'assignee' => 'John',
            'status' => 'in_progress',
            'priority' => 'high',
        ]);
        $this->assertSame('2026-11-25', Todo::first()->due_date->toDateString());
    }

    public function test_status_defaults_to_pending_and_time_tracked_defaults_to_zero(): void
    {
        $this->postJson('/api/todos', [
            'title' => 'Prepare Postman collection',
            'due_date' => '2026-11-30',
            'priority' => 'low',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.time_tracked', 0)
            ->assertJsonPath('data.assignee', null);

        $this->assertDatabaseHas('todos', ['title' => 'Prepare Postman collection', 'status' => 'pending']);
    }

    public function test_null_status_also_defaults_to_pending(): void
    {
        $this->postJson('/api/todos', [
            'title' => 'Null status',
            'due_date' => '2026-11-30',
            'status' => null,
            'priority' => 'medium',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_due_date_today_is_allowed(): void
    {
        $this->postJson('/api/todos', [
            'title' => 'Daily standup',
            'due_date' => '2026-11-18',
            'priority' => 'medium',
        ])->assertCreated();
    }

    public function test_due_date_cannot_be_in_the_past(): void
    {
        $this->postJson('/api/todos', [
            'title' => 'Old task',
            'due_date' => '2026-11-17',
            'priority' => 'medium',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['due_date' => 'The due date cannot be in the past.']);

        $this->assertDatabaseCount('todos', 0);
    }

    public function test_due_date_must_use_the_yyyy_mm_dd_format(): void
    {
        $this->postJson('/api/todos', [
            'title' => 'Wrong date format',
            'due_date' => '30-11-2026',
            'priority' => 'low',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('due_date');
    }

    public function test_required_fields_are_validated(): void
    {
        $this->postJson('/api/todos', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'due_date', 'priority'])
            ->assertJsonMissingValidationErrors(['assignee', 'status', 'time_tracked']);
    }

    public function test_status_and_priority_must_be_valid_enum_values(): void
    {
        $this->postJson('/api/todos', [
            'title' => 'Invalid enums',
            'due_date' => '2026-11-30',
            'status' => 'done',
            'priority' => 'urgent',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'status' => 'pending, open, in_progress, completed',
                'priority' => 'low, medium, high',
            ]);
    }

    public function test_time_tracked_must_be_a_non_negative_number(): void
    {
        $payload = ['title' => 'Tracked', 'due_date' => '2026-11-30', 'priority' => 'low'];

        $this->postJson('/api/todos', [...$payload, 'time_tracked' => 'abc'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('time_tracked');

        $this->postJson('/api/todos', [...$payload, 'time_tracked' => -1])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('time_tracked');
    }

    public function test_validation_errors_are_json_even_without_accept_header(): void
    {
        $this->post('/api/todos', ['title' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'due_date', 'priority']);
    }
}