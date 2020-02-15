<?php

namespace App\Http\Livewire;

use App\Models\Todo;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class TodoList extends Component
{
    use WithPagination;

    const PAGINATION = 5;
    const TRANSITION_STATUS = [
        'active' => 'completed',
        'completed' => 'active',
    ];

    /**
     * Filter todo.
     *
     * @var string
     */
    public $filter;

    /**
     * Current page items are checked ?
     *
     * @var bool
     */
    public $checkItemsOnCurrentPage;

    /**
     * Render the component.
     *
     * @return View
     */
    public function render(): View
    {
        $todos = $this->filter == 'all' ? Todo::paginate(self::PAGINATION) : Todo::status($this->filter)->paginate(self::PAGINATION);

        if (collect($todos->items())->where('status', 'completed')->count() == self::PAGINATION) {
            $this->checkItemsOnCurrentPage = true;
        } else {
            $this->checkItemsOnCurrentPage = false;
        }

        return view('livewire.todo-list', compact('todos'));
    }

    /**
     * Mount component.
     */
    public function mount(): void
    {
        $this->checkItemsOnCurrentPage = false;
        $this->filter = 'all';
    }

    /**
     * Logic when $this->fitler is updated.
     *
     * @param string $value
     */
    public function updatedFilter(string $value): void
    {
        $this->gotoPage(1);
    }

    /**
     * Add a new todo.
     *
     * @param string $value
     * @param int|null $lastPage
     * @param int|null $totalItems
     */
    public function addTodo(string $value, ?int $lastPage, ?int $totalItems): void
    {
        $todo = new Todo();
        $todo->todo = $value;
        // status is set by default, don't need to manage it
        $todo->save();

        // determine on which page we must redirect
        // depends on number of items on the last page
        if ($totalItems % self::PAGINATION == 0) {
            $this->gotoPage($lastPage + 1);
        } else {
            $this->gotoPage($lastPage);
        }
    }

    /**
     * Delete a todo.
     *
     * @param int $todoId
     * @param int $countTodosOnCurrentPage
     */
    public function deleteTodo(int $todoId, int $countTodosOnCurrentPage): void
    {
        Todo::destroy($todoId);

        if ($countTodosOnCurrentPage == 1 && $this->page != 1) {
            $this->gotoPage($this->page - 1);
        }
    }

    /**
     * Update a todo's status.
     *
     * @param int $todoId
     * @param string $currentStatus
     */
    public function updateTodoStatus(int $todoId, string $currentStatus): void
    {
        Todo::where('id', $todoId)
            ->update([
                'status' => self::TRANSITION_STATUS[$currentStatus],
            ]);
    }

    /**
     * Update all todos status on the current page.
     *
     * @param string $todosIds
     */
    public function updateTodosStatusOnCurrentPage(string $todosIds): void
    {
        $desiredStatus = $this->checkItemsOnCurrentPage ? 'active' : 'completed';

        Todo::whereIn('id', json_decode($todosIds))
            ->statusNot($desiredStatus)
            ->update([
                'status' => $this->checkItemsOnCurrentPage ? 'active' : 'completed',
            ]);
    }

    /**
     * Override the pagination view.
     *
     * @return string
     */
    public function paginationView(): string
    {
        return 'vendor/pagination/default';
    }
}
