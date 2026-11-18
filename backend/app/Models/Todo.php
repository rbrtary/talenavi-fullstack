<?php

namespace App\Models;

use App\Enums\TodoPriority;
use App\Enums\TodoStatus;
use Database\Factories\TodoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'assignee', 'due_date', 'time_tracked', 'status', 'priority'])]
class Todo extends Model
{
    /** @use HasFactory<TodoFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'due_date' => 'date:Y-m-d',
            'time_tracked' => 'float',
            'status' => TodoStatus::class,
            'priority' => TodoPriority::class,
        ];
    }

    #[Scope]
    protected function filter(Builder $query, array $filters): void
    {
        $query
            ->when(filled($filters['title'] ?? null), fn (Builder $q) => $q->where('title', 'like', '%'.$filters['title'].'%'))
            ->when(filled($filters['assignee'] ?? null), fn (Builder $q) => $q->whereIn('assignee', $filters['assignee']))
            ->when(filled($filters['start'] ?? null), fn (Builder $q) => $q->whereDate('due_date', '>=', $filters['start']))
            ->when(filled($filters['end'] ?? null), fn (Builder $q) => $q->whereDate('due_date', '<=', $filters['end']))
            ->when(filled($filters['min'] ?? null), fn (Builder $q) => $q->where('time_tracked', '>=', (float) $filters['min']))
            ->when(filled($filters['max'] ?? null), fn (Builder $q) => $q->where('time_tracked', '<=', (float) $filters['max']))
            ->when(filled($filters['status'] ?? null), fn (Builder $q) => $q->whereIn('status', $filters['status']))
            ->when(filled($filters['priority'] ?? null), fn (Builder $q) => $q->whereIn('priority', $filters['priority']));
    }
}