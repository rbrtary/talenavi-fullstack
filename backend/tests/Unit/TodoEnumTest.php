<?php

namespace Tests\Unit;

use App\Enums\TodoPriority;
use App\Enums\TodoStatus;
use PHPUnit\Framework\TestCase;

class TodoEnumTest extends TestCase
{
    public function test_status_values_match_the_requirement(): void
    {
        $this->assertSame(['pending', 'open', 'in_progress', 'completed'], TodoStatus::values());
    }

    public function test_priority_values_match_the_requirement(): void
    {
        $this->assertSame(['low', 'medium', 'high'], TodoPriority::values());
    }
}