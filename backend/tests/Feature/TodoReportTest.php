<?php

namespace Tests\Feature;

use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class TodoReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Todo::factory()->createMany([
            ['title' => 'Design database schema', 'assignee' => 'John', 'due_date' => '2026-12-01', 'time_tracked' => 3.5, 'status' => 'completed', 'priority' => 'high'],
            ['title' => 'Build todo API', 'assignee' => 'Doe', 'due_date' => '2026-12-05', 'time_tracked' => 5, 'status' => 'in_progress', 'priority' => 'high'],
            ['title' => 'Slice login page', 'assignee' => 'Robert', 'due_date' => '2026-12-10', 'time_tracked' => 2, 'status' => 'open', 'priority' => 'medium'],
            ['title' => 'Write API documentation', 'assignee' => null, 'due_date' => '2026-12-20', 'time_tracked' => 0, 'status' => 'pending', 'priority' => 'low'],
        ]);
    }

    public function test_export_downloads_an_xlsx_file(): void
    {
        $response = $this->get('/api/todos/export');

        $response->assertOk()->assertDownload();
        $this->assertStringContainsString('spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertMatchesRegularExpression('/todo-report-\d{8}_\d{6}\.xlsx/', $response->headers->get('Content-Disposition'));
    }

    public function test_excel_contains_headings_rows_and_a_summary_row(): void
    {
        $rows = $this->downloadReport('/api/todos/export');

        $this->assertCount(6, $rows);
        $this->assertSame(['title', 'assignee', 'due_date', 'time_tracked', 'status', 'priority'], $rows[0]);
        $this->assertSame(['Design database schema', 'John', '2026-12-01', '3.50', 'completed', 'high'], $rows[1]);
        $this->assertSame(['Write API documentation', null, '2026-12-20', '0.00', 'pending', 'low'], $rows[4]);
        $this->assertSame(['Total Todos', '4', 'Total Time Tracked', '10.50', null, null], $rows[5]);
    }

    public function test_excel_report_applies_the_filters_to_rows_and_summary(): void
    {
        $rows = $this->downloadReport('/api/todos/export?assignee=John,Doe,Robert&status=completed,in_progress,open&priority=high&start=2026-12-01&end=2026-12-31&min=1&title=a');

        $this->assertCount(4, $rows);
        $this->assertSame('Design database schema', $rows[1][0]);
        $this->assertSame('Build todo API', $rows[2][0]);
        $this->assertSame(['Total Todos', '2', 'Total Time Tracked', '8.50', null, null], $rows[3]);
    }

    public function test_report_without_matching_todos_still_has_a_summary_row(): void
    {
        $rows = $this->downloadReport('/api/todos/export?assignee=Nobody');

        $this->assertCount(2, $rows);
        $this->assertSame(['Total Todos', '0', 'Total Time Tracked', '0.00', null, null], $rows[1]);
    }

    public function test_filter_title_is_a_partial_match(): void
    {
        $this->getJson('/api/todos?title=api')
            ->assertOk()
            ->assertJsonPath('data.*.title', ['Build todo API', 'Write API documentation']);
    }

    public function test_filter_assignee_accepts_multiple_names(): void
    {
        $this->getJson('/api/todos?assignee=John,Doe')
            ->assertOk()
            ->assertJsonPath('data.*.assignee', ['John', 'Doe']);

        $this->getJson('/api/todos?assignee=John,%20Robert%20')
            ->assertJsonPath('data.*.assignee', ['John', 'Robert']);
    }

    public function test_filter_due_date_range_is_inclusive(): void
    {
        $this->getJson('/api/todos?start=2026-12-05&end=2026-12-10')
            ->assertOk()
            ->assertJsonPath('data.*.title', ['Build todo API', 'Slice login page']);

        $this->getJson('/api/todos?start=2026-12-10')->assertJsonCount(2, 'data');
        $this->getJson('/api/todos?end=2026-12-01')->assertJsonCount(1, 'data');
    }

    public function test_filter_time_tracked_range(): void
    {
        $this->getJson('/api/todos?min=2&max=5')
            ->assertOk()
            ->assertJsonPath('data.*.time_tracked', [3.5, 5, 2]);

        $this->getJson('/api/todos?max=0')
            ->assertJsonPath('data.*.title', ['Write API documentation']);
    }

    public function test_filter_status_and_priority_accept_multiple_values(): void
    {
        $this->getJson('/api/todos?status=pending,in_progress')
            ->assertOk()
            ->assertJsonPath('data.*.status', ['in_progress', 'pending']);

        $this->getJson('/api/todos?priority=low,high')
            ->assertJsonPath('data.*.priority', ['high', 'high', 'low']);
    }

    public function test_json_list_includes_the_same_summary_as_the_excel(): void
    {
        $this->getJson('/api/todos?priority=high')
            ->assertOk()
            ->assertJsonPath('summary.total_todos', 2)
            ->assertJsonPath('summary.total_time_tracked', 8.5);
    }

    public function test_invalid_filter_values_are_rejected(): void
    {
        $this->getJson('/api/todos/export?status=pending,done&priority=urgent&start=2026-13-01&min=5&max=1')
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'status.1' => 'Each status must be one of: pending, open, in_progress, completed.',
                'priority.0' => 'Each priority must be one of: low, medium, high.',
                'start',
                'max' => 'The max value must be greater than or equal to the min value.',
            ]);
    }

    public function test_end_date_must_not_be_before_start_date(): void
    {
        $this->getJson('/api/todos/export?start=2026-12-10&end=2026-12-01')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['end' => 'The end date must be after or equal to the start date.']);
    }

    public function test_single_sided_ranges_are_valid(): void
    {
        $this->getJson('/api/todos?end=2026-12-31')->assertOk();
        $this->getJson('/api/todos?max=10')->assertOk();
    }

    private function downloadReport(string $uri): array
    {
        $response = $this->get($uri);
        $response->assertOk()->assertDownload();

        $path = $response->baseResponse->getFile()->getPathname();
        $rows = IOFactory::load($path)->getActiveSheet()->toArray();
        @unlink($path);

        return $rows;
    }
}