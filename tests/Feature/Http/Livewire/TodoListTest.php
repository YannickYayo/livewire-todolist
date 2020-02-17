<?php

namespace Tests\Feature\Http\Livewire;

use App\Http\Livewire\TodoList;
use App\Models\Todo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire;
use Tests\TestCase;

class TodoListTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

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
        factory(Todo::class, $this->paginationLimit);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Todo List');
        $response->assertViewIs('todo-list');
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
        $newTodo = $this->faker->realText(40);

        Livewire::test(TodoList::class)
            ->assertViewHas('todos', function ($todos) {
                return $todos->total() == 0;
            })
            ->call('addTodo', $newTodo)
            ->assertViewHas('todos', function ($todos) {
                return $todos->total() == 1;
            })
            ->assertSee(e($newTodo))
            ->assertSee('wire:keydown.enter="addTodo');

        $this->assertEquals(1, Todo::all()->count());
    }

    public function test_show_the_last_page_after_adding_a_todo_when_a_new_page_is_created(): void
    {
        factory(Todo::class, $this->paginationLimit)->create();
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
                return $todos->total() == $countTodos + 1;
            });
    }

    public function test_can_delete_a_todo(): void
    {
        factory(Todo::class, $this->paginationLimit)->create();
        $todosCreated = Todo::paginate($this->paginationLimit);
        $todoToDelete = $todosCreated->where('id', 1)->first();

        Livewire::test(TodoList::class)
            ->assertViewHas('todos', function ($todos) use ($todosCreated) {
                return $todos->total() == $todosCreated->count();
            })
            ->call('deleteTodo', $todoToDelete->id, $this->paginationLimit)
            ->assertViewHas('todos', function ($todos) use ($todosCreated) {
                return $todos->total() == $todosCreated->count() - 1;
            })
            ->assertDontSee($todoToDelete->todo)
            ->assertSee('wire:click="deleteTodo');

        $this->assertDeleted('todos', $todoToDelete->toArray());
    }

    public function test_go_to_previous_page_if_delete_last_todo_on_current_page(): void
    {
        factory(Todo::class, $this->paginationLimit + 1)->create();
        $todosCreated = Todo::paginate($this->paginationLimit);
        $todoToDelete = $todosCreated->where('id', 1)->first();
        $currentPage = 2;

        Livewire::test(TodoList::class)
            ->set('page', $currentPage)
            ->call('deleteTodo', $todoToDelete->id, 1)
            ->assertSet('page', $currentPage - 1)
            ->assertViewHas('todos', function ($todos) use ($todosCreated) {
                return $todos->total() == $todosCreated->total() - 1;
            })
            ->assertViewHas('todos', function ($todos) {
                return $todos->count() == $this->paginationLimit;
            });
    }

    public function test_stay_on_the_same_page_after_deleting_a_todo_if_items_still_present_on_the_current_page(): void
    {
        factory(Todo::class, $this->paginationLimit + 2)->create();
        $todosCreated = Todo::paginate($this->paginationLimit);
        $todoToDelete = $todosCreated->where('id', 1)->first();
        $currentPage = 2;

        Livewire::test(TodoList::class)
            ->set('page', $currentPage)
            ->call('deleteTodo', $todoToDelete->id, 2)
            ->assertSet('page', $currentPage)
            ->assertViewHas('todos', function ($todos) use ($todosCreated) {
                return $todos->total() == $todosCreated->total() - 1;
            })
            ->assertViewHas('todos', function ($todos) {
                return $todos->count() == 1;
            });
    }

    public function test_can_update_a_todo_status(): void
    {
        factory(Todo::class)->create(['status' => 'active']);
        $activeTodo = Todo::status('active')->first();
        factory(Todo::class)->create(['status' => 'completed']);
        $completedTodo = Todo::status('completed')->first();

        Livewire::test(TodoList::class)
            ->call('updateTodoStatus', $activeTodo->id, $activeTodo->status)
            ->assertViewHas('todos', function ($todos) use ($activeTodo) {
                return $todos->where('id', $activeTodo->id)->first()->status == 'completed';
            })
            ->call('updateTodoStatus', $completedTodo->id, $completedTodo->status)
            ->assertViewHas('todos', function ($todos) use ($completedTodo) {
                return $todos->where('id', $completedTodo->id)->first()->status == 'active';
            })
            ->call('updateTodoStatus', $activeTodo->id, Todo::find($activeTodo->id)->status)
            ->assertViewHas('todos', function ($todos) use ($activeTodo) {
                return $todos->where('id', $activeTodo->id)->first()->status == 'active';
            })
            ->assertSee('wire:click="updateTodoStatus('.$activeTodo->id.', \''.$activeTodo->status.'\')');

        $this->assertEquals('active', Todo::find($activeTodo->id)->status);
        $this->assertEquals('active', Todo::find($completedTodo->id)->status);
    }

    public function test_can_update_all_todos_status_on_current_page_to_completed(): void
    {
        factory(Todo::class, $this->paginationLimit + 1)->create(['status' => 'active']);
        $activeTodos = Todo::status('active')->paginate($this->paginationLimit);
        $todosIds = collect($activeTodos->items())->map->id->toArray();

        Livewire::test(TodoList::class)
            ->set('page', 1)
            ->set('checkItemsOnCurrentPage', false)
            ->call('updateTodosStatusOnCurrentPage', json_encode($todosIds))
            ->assertSet('checkItemsOnCurrentPage', true)
            ->assertViewHas('todos', function ($todos) {
                $countCompletedTodos = collect($todos->items())->where('status', 'completed')->count();

                return $countCompletedTodos == 5;
            })
            ->assertSee('wire:click="updateTodosStatusOnCurrentPage(\''.json_encode($todosIds).'\')');

        $this->assertEquals(5, Todo::status('completed')->count());
        $this->assertEquals('active', Todo::orderBy('id', 'desc')->first()->status);
    }

    public function test_can_update_all_todos_status_on_current_page_to_active(): void
    {
        factory(Todo::class, $this->paginationLimit + 1)->create(['status' => 'completed']);
        $completedTodos = Todo::status('completed')->paginate($this->paginationLimit);
        $todosIds = collect($completedTodos->items())->map->id->toArray();

        Livewire::test(TodoList::class)
            ->set('page', 1)
            ->set('checkItemsOnCurrentPage', true)
            ->call('updateTodosStatusOnCurrentPage', json_encode($todosIds))
            ->assertSet('checkItemsOnCurrentPage', false)
            ->assertViewHas('todos', function ($todos) {
                $countActiveTodos = collect($todos->items())->where('status', 'active')->count();

                return $countActiveTodos == 5;
            });

        $this->assertEquals(5, Todo::status('active')->count());
        $this->assertEquals('completed', Todo::orderBy('id', 'desc')->first()->status);
    }

    public function test_check_items_on_current_page_property_is_true_when_all_items_are_completed(): void
    {
        factory(Todo::class, 10)->create(['status' => 'completed']);

        Livewire::test(TodoList::class)
            ->set('page', 1)
            ->assertSet('checkItemsOnCurrentPage', true)
            ->set('page', 2)
            ->assertSet('checkItemsOnCurrentPage', true);
    }

    public function test_check_items_on_current_page_property_is_false_when_not_all_items_are_completed(): void
    {
        factory(Todo::class, 3)->create(['status' => 'completed']);
        factory(Todo::class, 3)->create(['status' => 'active']);

        Livewire::test(TodoList::class)
            ->set('page', 1)
            ->assertSet('checkItemsOnCurrentPage', false)
            ->set('page', 2)
            ->assertSet('checkItemsOnCurrentPage', false);
    }

    public function test_check_items_on_current_page_property_is_right_after_deletion_or_filtering(): void
    {
        factory(Todo::class, $this->paginationLimit)->create(['status' => 'completed']);
        factory(Todo::class, 1)->create(['status' => 'active']);
        $activeTodo = Todo::status('active')->first();

        Livewire::test(TodoList::class)
            ->set('page', 1)
            ->assertSet('checkItemsOnCurrentPage', true)
            ->set('filter', 'active')
            ->assertSet('checkItemsOnCurrentPage', false)
            ->set('filter', 'completed')
            ->assertSet('checkItemsOnCurrentPage', true)
            ->set('filter', 'all')
            ->assertSet('checkItemsOnCurrentPage', true)
            ->set('page', 2)
            ->assertSet('checkItemsOnCurrentPage', false)
            ->call('deleteTodo', $activeTodo->id, 1)
            ->assertSet('checkItemsOnCurrentPage', true);
    }

    public function test_can_filter_todos(): void
    {
        factory(Todo::class, 10)->create(['status' => 'completed']);
        factory(Todo::class, 15)->create(['status' => 'active']);

        Livewire::test(TodoList::class)
            ->set('filter', 'all')
            ->assertViewHas('todos', function ($todos) {
                return $todos->total() == 25;
            })
            ->set('filter', 'active')
            ->assertViewHas('todos', function ($todos) {
                return $todos->total() == 15;
            })
            ->set('filter', 'completed')
            ->assertViewHas('todos', function ($todos) {
                return $todos->total() == 10;
            })
            ->assertSee('wire:click="$set(\'filter\', \'all\')"')
            ->assertSee('wire:click="$set(\'filter\', \'active\')"')
            ->assertSee('wire:click="$set(\'filter\', \'completed\')"');
    }

    public function test_go_to_page_1_when_filtering(): void
    {
        factory(Todo::class, 8)->create();

        Livewire::test(TodoList::class)
            ->set('filter', 'all')
            ->set('page', 2)
            ->set('filter', 'active')
            ->assertSet('page', 1);
    }

    public function test_can_delete_completed_todos_on_current_page(): void
    {
        factory(Todo::class, 10)->create(['status' => 'completed']);
        $completedTodos = Todo::status('completed')->paginate($this->paginationLimit);
        $todosIds = collect($completedTodos->items())->map->id->toArray();

        Livewire::test(TodoList::class)
            ->set('page', 1)
            ->assertSee('wire:click="deleteCurrentCompletedTodos(\''.json_encode($todosIds).'\', '.$completedTodos->hasMorePages().')')
            ->assertViewHas('todos', function ($todos) {
                return $todos->total() == 10;
            })
            ->call('deleteCurrentCompletedTodos', json_encode($todosIds), $completedTodos->hasMorePages())
            ->assertViewHas('todos', function ($todos) {
                return $todos->total() == 5;
            });

        foreach ($completedTodos->items() as $todo) {
            $this->assertDeleted('todos', $todo->toArray());
        }
    }

    public function test_go_to_previous_page_after_deleting_current_completed_todos_when_there_is_no_more_pages(): void
    {
        factory(Todo::class, 10)->create(['status' => 'completed']);
        $completedTodos = Todo::status('completed')->paginate($this->paginationLimit, ['*'], 'page', 2);
        $todosIds = collect($completedTodos->items())->map->id->toArray();

        Livewire::test(TodoList::class)
            ->set('page', 2)
            ->call('deleteCurrentCompletedTodos', json_encode($todosIds))
            ->assertSet('page', 1);
    }

    public function test_stay_on_current_page_after_deleting_current_completed_todos_when_there_is_more_pages(): void
    {
        factory(Todo::class, 15)->create(['status' => 'completed']);
        $completedTodos = Todo::status('completed')->paginate($this->paginationLimit, ['*'], 'page', 2);
        $todosIds = collect($completedTodos->items())->map->id->toArray();

        Livewire::test(TodoList::class)
            ->set('page', 2)
            ->call('deleteCurrentCompletedTodos', json_encode($todosIds), $completedTodos->hasMorePages())
            ->assertSet('page', 2);
    }

    public function test_can_search_todos(): void
    {
        factory(Todo::class)->create(['todo' => 'A new todo']);
        factory(Todo::class)->create(['todo' => 'A todo made in second time']);
        factory(Todo::class)->create(['todo' => 'A new todo, but the third']);

        Livewire::test(TodoList::class)
            ->assertViewHas('todos', function ($todos) {
                return $todos->total() == 3;
            })
            ->set('search', 'new todo')
            ->assertViewHas('todos', function ($todos) {
                return $todos->total() == 2;
            })
            ->assertSee('A new todo')
            ->assertSee('A new todo, but the third')
            ->set('search', 'second time')
            ->assertViewHas('todos', function ($todos) {
                return $todos->total() == 1;
            })
            ->assertSee('A todo made in second time')
            ->assertSee('input')
            ->assertSee('type="search"')
            ->assertSee('wire:model.debounce.700ms="search"');
    }

    public function test_go_to_page_1_when_searching(): void
    {
        factory(Todo::class, 10);

        Livewire::test(TodoList::class)
            ->set('page', 2)
            ->set('search', 'some search')
            ->assertSet('page', 1);
    }
}
