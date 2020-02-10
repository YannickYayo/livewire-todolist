<?php

namespace Tests\Feature\Http\Livewire;

use App\Http\Livewire\TodoList;
use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire;
use Tests\TestCase;

class TodoListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pagination limit.
     *
     * @var int
     */
    private $paginationLimit;

    public function setUp(): void
    {
        parent::setUp();
        $this->paginationLimit = 5;
    }

    public function test_can_see_todos(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Todo List');
    }

    public function test_see_limit_todos_at_a_time_by_default(): void
    {
        factory(Todo::class, $this->paginationLimit + 1)->create();

        Livewire::test(TodoList::class)
            ->assertViewHas('todos', function ($todos) {
                return $todos->count() == $this->paginationLimit;
            });
    }

    public function test_can_add_a_todo(): void
    {
        Livewire::test(TodoList::class)
            ->assertViewHas('todos', function ($todos) {
                return $todos->count() == 0;
            })
            ->call('addTodo', 'New Todo')
            ->assertViewHas('todos', function ($todos) {
                return $todos->count() == 1;
            })
            ->assertSee('New Todo')
            ->assertSee('wire:keydown.enter="addTodo');

        $this->assertEquals(1, Todo::all()->count());
    }

    public function test_show_the_last_page_after_adding_a_todo_when_a_new_page_is_created(): void
    {
        factory(Todo::class, 5)->create();
        $todos = Todo::paginate($this->paginationLimit);
        $currentPage = 1;

        Livewire::test(TodoList::class)
            ->set('page', $currentPage)
            ->assertSet('page', $currentPage)
            ->call('addTodo', 'New Todo', $currentPage, $todos->total())
            ->assertSet('page', $currentPage + 1)
            ->assertViewHas('todos', function ($todos) {
                return $todos->count() == 1;
            });
    }

    public function test_show_the_last_page_after_adding_a_todo_when_no_new_page_is_created(): void
    {
        $countTodos = 16;
        factory(Todo::class, $countTodos)->create();
        $todos = Todo::paginate($this->paginationLimit);
        $currentPage = 2;
        $lastPage = intval(ceil($countTodos / $this->paginationLimit));

        Livewire::test(TodoList::class)
            ->set('page', $currentPage)
            ->assertSet('page', $currentPage)
            ->call('addTodo', 'New Todo', $lastPage, $todos->total())
            ->assertSet('page', $lastPage)
            ->assertViewHas('todos', function ($todos) {
                return $todos->count() == 2;
            });
    }

    public function test_stay_on_the_same_page_after_adding_a_todo_when_number_of_items_is_under_limit(): void
    {
        $countTodos = 4;
        factory(Todo::class, $countTodos)->create();
        $todos = Todo::paginate($this->paginationLimit);
        $currentPage = 1;

        Livewire::test(TodoList::class)
            ->set('page', $currentPage)
            ->assertSet('page', $currentPage)
            ->call('addTodo', 'New Todo', $currentPage, $todos->total())
            ->assertSet('page', $currentPage)
            ->assertViewHas('todos', function ($todos) use ($countTodos) {
                return $todos->count() == $countTodos + 1;
            });
    }
}
