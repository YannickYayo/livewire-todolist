<?php

namespace Tests\Unit\Models;

use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_set_default_status(): void
    {
        $todo = Todo::create([
            'todo' => 'test',
        ]);

        $this->assertEquals('active', $todo->status);
    }

    public function test_check_completed_toto(): void
    {
        $activeTodo = factory(Todo::class)->create(['status' => 'active']);
        $completedTodo = factory(Todo::class)->create(['status' => 'completed']);

        $this->assertFalse($activeTodo->isCompleted());
        $this->asserttrue($completedTodo->isCompleted());
    }
}
