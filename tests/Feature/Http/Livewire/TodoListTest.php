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
    use WithFaker;

    public function paginationProvider()
    {
        return [
            [5],
            [6],
            [10],
        ];
    }

    public function test_can_see_todos(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewIs('todo-list');
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_see_limit_todos_at_a_time_by_default(int $pagination): void
    {
        factory(Todo::class, $pagination + 1)->create();

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->assertViewHas('todos', function ($todos) use ($pagination) {
                return $todos->count() == $pagination;
            });
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_can_add_a_todo(int $pagination): void
    {
        $newTodo = $this->faker->realText(40);

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->assertViewHas('todos', function ($todos) {
                return $todos->total() == 0;
            })
            ->set('newTodo', $newTodo)
            ->call('addTodo')
            ->assertViewHas('todos', function ($todos) {
                return $todos->total() == 1;
            })
            ->assertSee($newTodo);

        $this->assertEquals(1, Todo::all()->count());
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_empty_todo_trigger_an_error_when_add_a_todo(int $pagination): void
    {
        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->set('newTodo', '')
            ->call('addTodo')
            ->assertHasErrors(['newTodo' => 'required']);
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_fill_todo_dont_trigger_an_error_when_add_a_todo(int $pagination): void
    {
        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->set('newTodo', 'Test')
            ->call('addTodo')
            ->assertHasNoErrors('newTodo');
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_show_the_last_page_after_adding_a_todo_when_a_new_page_is_created(int $pagination): void
    {
        factory(Todo::class, $pagination)->create();
        $todos = Todo::paginate($pagination);
        $currentPage = 1;
        $newTodo = $this->faker->realText(40);

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->set('page', $currentPage)
            ->set('newTodo', $newTodo)
            ->call('addTodo', $currentPage, $todos->total())
            ->assertSet('page', $currentPage + 1)
            ->assertViewHas('todos', function ($todos) {
                return $todos->count() == 1;
            });
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_show_the_last_page_after_adding_a_todo_when_no_new_page_is_created(int $pagination): void
    {
        $itemsOnLastPage = 3; // must be < to 5
        $countTodos = ($pagination * 3) + $itemsOnLastPage; // make 4 pages, with only 3 items on the last page
        factory(Todo::class, $countTodos)->create();
        $todos = Todo::paginate($pagination);
        $currentPage = 2;
        $lastPage = intval(ceil($countTodos / $pagination));
        $newTodo = $this->faker->realText(40);

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->set('page', $currentPage)
            ->set('newTodo', $newTodo)
            ->call('addTodo', $lastPage, $todos->total())
            ->assertSet('page', $lastPage)
            ->assertViewHas('todos', function ($todos) use ($itemsOnLastPage) {
                return $todos->count() == $itemsOnLastPage + 1;
            });
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_stay_on_the_same_page_after_adding_a_todo_when_number_of_items_is_under_limit(int $pagination): void
    {
        $countTodos = $pagination - 1;
        factory(Todo::class, $countTodos)->create();
        $todos = Todo::paginate($pagination);
        $currentPage = 1;
        $newTodo = $this->faker->realText(40);

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->set('page', $currentPage)
            ->set('newTodo', $newTodo)
            ->call('addTodo', $currentPage, $todos->total())
            ->assertSet('page', $currentPage)
            ->assertViewHas('todos', function ($todos) use ($countTodos) {
                return $todos->total() == $countTodos + 1;
            });
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_can_delete_a_todo(int $pagination): void
    {
        factory(Todo::class, $pagination)->create();
        $todosCreated = Todo::paginate($pagination);
        $todoToDelete = $todosCreated->where('id', rand(1, $pagination))->first();

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->assertViewHas('todos', function ($todos) use ($todosCreated) {
                return $todos->total() == $todosCreated->count();
            })
            ->call('deleteTodo', $todoToDelete->id, $pagination)
            ->assertViewHas('todos', function ($todos) use ($todosCreated) {
                return $todos->total() == $todosCreated->count() - 1;
            })
            ->assertDontSee($todoToDelete->todo);

        $this->assertDeleted('todos', $todoToDelete->toArray());
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_go_to_previous_page_if_delete_last_todo_on_current_page(int $pagination): void
    {
        factory(Todo::class, $pagination + 1)->create();
        $todosCreated = Todo::paginate($pagination, ['*'], 'page', 2);
        $todoToDelete = $todosCreated->first();
        $currentPage = 2;

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->set('page', $currentPage)
            ->call('deleteTodo', $todoToDelete->id, 1)
            ->assertSet('page', $currentPage - 1)
            ->assertViewHas('todos', function ($todos) use ($todosCreated) {
                return $todos->total() == $todosCreated->total() - 1;
            })
            ->assertViewHas('todos', function ($todos) use ($pagination) {
                return $todos->count() == $pagination;
            });
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_stay_on_the_same_page_after_deleting_a_todo_if_items_still_present_on_the_current_page(int $pagination): void
    {
        factory(Todo::class, $pagination + 2)->create();
        $currentPage = 2;
        $todosCreated = Todo::paginate($pagination, ['*'], 'page', $currentPage);
        $todoToDelete = $todosCreated->first();

        Livewire::test(TodoList::class, ['pagination' => $pagination])
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

    /**
     * @dataProvider paginationProvider
     */
    public function test_can_update_a_todo_status(int $pagination): void
    {
        factory(Todo::class)->create(['status' => 'active']);
        $activeTodo = Todo::status('active')->first();
        factory(Todo::class)->create(['status' => 'completed']);
        $completedTodo = Todo::status('completed')->first();

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->call('updateTodoStatus', $activeTodo->id, $activeTodo->status)
            ->assertViewHas('todos', function ($todos) use ($activeTodo) {
                return $todos->where('id', $activeTodo->id)->first()->status == 'completed';
            })
            ->call('updateTodoStatus', $completedTodo->id, $completedTodo->status)
            ->assertViewHas('todos', function ($todos) use ($completedTodo) {
                return $todos->where('id', $completedTodo->id)->first()->status == 'active';
            });

        $this->assertEquals('completed', Todo::find($activeTodo->id)->status);
        $this->assertEquals('active', Todo::find($completedTodo->id)->status);
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_can_update_all_todos_status_on_current_page_to_completed(int $pagination): void
    {
        factory(Todo::class, $pagination + 1)->create(['status' => 'active']);
        $activeTodos = Todo::status('active')->paginate($pagination);
        $todosIds = collect($activeTodos->items())->map->id->toArray();

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->set('page', 1)
            ->set('checkItemsOnCurrentPage', false)
            ->call('updateTodosStatusOnCurrentPage', json_encode($todosIds))
            ->assertSet('checkItemsOnCurrentPage', true)
            ->assertViewHas('todos', function ($todos) use ($pagination) {
                $countCompletedTodos = collect($todos->items())->where('status', 'completed')->count();

                return $countCompletedTodos == $pagination;
            });

        $this->assertEquals($pagination, Todo::status('completed')->count());
        $this->assertEquals('active', Todo::orderBy('id', 'desc')->first()->status);
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_can_update_all_todos_status_on_current_page_to_active(int $pagination): void
    {
        factory(Todo::class, $pagination + 1)->create(['status' => 'completed']);
        $completedTodos = Todo::status('completed')->paginate($pagination);
        $todosIds = collect($completedTodos->items())->map->id->toArray();

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->set('page', 1)
            ->set('checkItemsOnCurrentPage', true)
            ->call('updateTodosStatusOnCurrentPage', json_encode($todosIds))
            ->assertSet('checkItemsOnCurrentPage', false)
            ->assertViewHas('todos', function ($todos) use ($pagination) {
                $countActiveTodos = collect($todos->items())->where('status', 'active')->count();

                return $countActiveTodos == $pagination;
            });

        $this->assertEquals($pagination, Todo::status('active')->count());
        $this->assertEquals('completed', Todo::orderBy('id', 'desc')->first()->status);
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_check_items_on_current_page_property_is_true_when_all_items_are_completed(int $pagination): void
    {
        factory(Todo::class, $pagination * 2)->create(['status' => 'completed']);
        factory(Todo::class, $pagination)->create(['status' => 'active']);

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->set('page', 1)
            ->assertSet('checkItemsOnCurrentPage', true)
            ->set('page', 2)
            ->assertSet('checkItemsOnCurrentPage', true)
            ->set('page', 3)
            ->assertSet('checkItemsOnCurrentPage', false);
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_check_items_on_current_page_property_is_true_on_last_page_when_all_items_are_completed_and_count_items_is_not_full(int $pagination): void
    {
        factory(Todo::class, ($pagination * 3) - 1)->create(['status' => 'completed']);

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->set('page', 3)
            ->assertSet('checkItemsOnCurrentPage', true);
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_check_items_on_current_page_property_is_false_when_not_all_items_are_completed(int $pagination): void
    {
        factory(Todo::class, $pagination / 2)->create(['status' => 'completed']);
        factory(Todo::class, $pagination / 2)->create(['status' => 'active']);
        factory(Todo::class, $pagination / 2)->create(['status' => 'completed']);
        factory(Todo::class, $pagination / 2)->create(['status' => 'active']);

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->set('page', 1)
            ->assertSet('checkItemsOnCurrentPage', false)
            ->set('page', 2)
            ->assertSet('checkItemsOnCurrentPage', false);
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_check_items_on_current_page_property_is_right_after_deletion_or_filtering(int $pagination): void
    {
        factory(Todo::class, $pagination)->create(['status' => 'completed']);
        factory(Todo::class, 1)->create(['status' => 'active']);
        $activeTodo = Todo::status('active')->first();

        Livewire::test(TodoList::class, ['pagination' => $pagination])
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

    /**
     * @dataProvider paginationProvider
     */
    public function test_can_filter_todos(int $pagination): void
    {
        $countCompletedTodos = $pagination * 2;
        $countActiveTodos = $pagination * 3;
        factory(Todo::class, $countCompletedTodos)->create(['status' => 'completed']);
        factory(Todo::class, $countActiveTodos)->create(['status' => 'active']);

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->set('filter', 'all')
            ->assertViewHas('todos', function ($todos) use ($countCompletedTodos, $countActiveTodos) {
                return $todos->total() == ($countCompletedTodos) + ($countActiveTodos);
            })
            ->set('filter', 'active')
            ->assertViewHas('todos', function ($todos) use ($countActiveTodos) {
                return $todos->total() == $countActiveTodos;
            })
            ->set('filter', 'completed')
            ->assertViewHas('todos', function ($todos) use ($countCompletedTodos) {
                return $todos->total() == $countCompletedTodos;
            });
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_go_to_page_1_when_filtering(int $pagination): void
    {
        factory(Todo::class, $pagination + 4)->create();

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->set('filter', 'all')
            ->set('page', 2)
            ->set('filter', 'active')
            ->assertSet('page', 1);
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_can_delete_completed_todos_on_current_page(int $pagination): void
    {
        $countCompletedTodos = $pagination * 2;
        factory(Todo::class, $countCompletedTodos)->create(['status' => 'completed']);
        $completedTodos = Todo::status('completed')->paginate($pagination);
        $todosIds = collect($completedTodos->items())->map->id->toArray();

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->set('page', 1)
            ->assertViewHas('todos', function ($todos) use ($countCompletedTodos) {
                return $todos->total() == $countCompletedTodos;
            })
            ->call('deleteCurrentCompletedTodos', json_encode($todosIds), $completedTodos->hasMorePages())
            ->assertViewHas('todos', function ($todos) use ($countCompletedTodos) {
                return $todos->total() == $countCompletedTodos / 2;
            });

        foreach ($completedTodos->items() as $todo) {
            $this->assertDeleted('todos', $todo->toArray());
        }
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_go_to_previous_page_after_deleting_current_completed_todos_when_there_is_no_more_pages(int $pagination): void
    {
        factory(Todo::class, $pagination * 2)->create(['status' => 'completed']);
        $completedTodos = Todo::status('completed')->paginate($pagination, ['*'], 'page', 2);
        $todosIds = collect($completedTodos->items())->map->id->toArray();

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->set('page', 2)
            ->call('deleteCurrentCompletedTodos', json_encode($todosIds))
            ->assertSet('page', 1);
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_stay_on_current_page_after_deleting_current_completed_todos_when_there_is_more_pages(int $pagination): void
    {
        factory(Todo::class, $pagination * 3)->create(['status' => 'completed']);
        $completedTodos = Todo::status('completed')->paginate($pagination, ['*'], 'page', 2);
        $todosIds = collect($completedTodos->items())->map->id->toArray();

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->set('page', 2)
            ->call('deleteCurrentCompletedTodos', json_encode($todosIds), $completedTodos->hasMorePages())
            ->assertSet('page', 2);
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_can_search_todos(int $pagination): void
    {
        factory(Todo::class)->create(['todo' => 'A new todo']);
        factory(Todo::class)->create(['todo' => 'A todo made in second time']);
        factory(Todo::class)->create(['todo' => 'A new todo, but the third']);

        Livewire::test(TodoList::class, ['pagination' => $pagination])
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
            ->assertSee('A todo made in second time');
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_go_to_page_1_when_searching(int $pagination): void
    {
        factory(Todo::class, $pagination * 2)->create();

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->set('page', 2)
            ->set('search', 'some search')
            ->assertSet('page', 1);
    }

    /**
     * @dataProvider paginationProvider
     */
    public function test_can_edit_a_todo(int $pagination): void
    {
        factory(Todo::class)->create();
        $todo = Todo::find(1);
        $newValue = 'New Value';

        Livewire::test(TodoList::class, ['pagination' => $pagination])
            ->assertSee('x-on:dblclick')
            ->assertSee('x-on:click.away')
            ->call('editTodo', $todo->id, $newValue)
            ->assertViewHas('todos', function ($todos) use ($todo, $newValue) {
                $newTodo = $todos->first();

                return $newTodo->todo != $todo->todo && $newTodo->todo == $newValue;
            });

        $this->assertEquals($newValue, Todo::find(1)->todo);
    }
}
