<?php

namespace Database\Seeders;

use App\Models\Todo;
use Illuminate\Database\Seeder;

class TodoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $todos = [
            ['Design database schema for todo module', 'John', '2026-11-25', 3.5, 'completed', 'high'],
            ['Build Create Todo API', 'John', '2026-11-27', 5, 'in_progress', 'high'],
            ['Build Excel report export API', 'Doe', '2026-12-01', 6.25, 'in_progress', 'high'],
            ['Slice login page (desktop & mobile)', 'Robert', '2026-12-03', 4, 'open', 'medium'],
            ['Write feature tests for todo API', 'Doe', '2026-12-05', 0, 'pending', 'medium'],
            ['Prepare Postman collection', 'Sarah', '2026-12-08', 1.5, 'open', 'low'],
            ['Weekly sync with product team', 'Sarah', '2026-12-10', 1, 'pending', 'low'],
            ['Fix validation bug on due date', 'Robert', '2026-12-12', 2.75, 'completed', 'high'],
            ['Update API documentation', null, '2026-12-15', 0, 'pending', 'low'],
            ['Deploy backend to staging', 'John', '2026-12-18', 0, 'pending', 'medium'],
            ['Code review: report filters', 'Doe', '2026-12-22', 1.25, 'open', 'medium'],
            ['Sprint retrospective', null, '2026-12-31', 0, 'pending', 'low'],
        ];

        foreach ($todos as [$title, $assignee, $dueDate, $timeTracked, $status, $priority]) {
            Todo::create([
                'title' => $title,
                'assignee' => $assignee,
                'due_date' => $dueDate,
                'time_tracked' => $timeTracked,
                'status' => $status,
                'priority' => $priority,
            ]);
        }
    }
}