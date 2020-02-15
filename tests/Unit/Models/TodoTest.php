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

    public function test_check_completed_todo(): void
    {
        $activeTodo = factory(Todo::class)->create(['status' => 'active']);
        $completedTodo = factory(Todo::class)->create(['status' => 'completed']);

        $this->assertFalse($activeTodo->isCompleted());
        $this->asserttrue($completedTodo->isCompleted());
    }

    public function test_check_scope_status(): void
    {
        factory(Todo::class, 2)->create(['status' => 'active']);
        factory(Todo::class, 3)->create(['status' => 'completed']);

        $this->assertEquals(2, Todo::status('active')->get()->count());
        $this->assertEquals(3, Todo::status('completed')->get()->count());
    }

    public function test_check_scope_status_not(): void
    {
        factory(Todo::class, 2)->create(['status' => 'active']);
        factory(Todo::class, 3)->create(['status' => 'completed']);

        $this->assertEquals(3, Todo::statusNot('active')->get()->count());
        $this->assertEquals(2, Todo::statusNot('completed')->get()->count());
    }
}
