<?php

namespace App\Http\Livewire;

use App\Models\Todo;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class TodoList extends Component
{
    use WithPagination;

    /**
     * Transition status.
     */
    private const TRANSITION_STATUS = [
        'active' => 'completed',
        'completed' => 'active',
    ];

    /**
     * Items per page.
     *
     * @var int
     */
    public $pagination;

    /**
     * Filter todo.
     *
     * @var string
     */
    public $filter = 'all';

    /**
     * Search todos.
     *
     * @var string
     */
    public $search = '';

    /**
     * Current page items are checked ?
     *
     * @var bool
     */
    public $checkItemsOnCurrentPage;

    /**
     * Queries string.
     *
     * @var array
     */
    protected $updatesQueryString = [
        'filter',
        ['search' => ['except' => '']],
        ['page' => ['except' => 1]],
    ];

    /**
     * Render the component.
     *
     * @return View
     */
    public function render(): View
    {
        $todos = $this->filter == 'all' ? Todo::search($this->search)->paginate($this->pagination) : Todo::search($this->search)->status($this->filter)->paginate($this->pagination);

        $todosCollection = collect($todos->items());
        if ($todosCollection->where('status', 'completed')->count() == $todosCollection->count()) {
            $this->checkItemsOnCurrentPage = true;
        } else {
            $this->checkItemsOnCurrentPage = false;
        }

        return view('livewire.todo-list', compact('todos'));
    }

    /**
     * Mount component.
     *
     * @param int $pagination
     */
    public function mount(int $pagination): void
    {
        $this->pagination = $pagination;
        $this->checkItemsOnCurrentPage = false;
        $this->fill(request()->only('search', 'page', 'filter'));
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
     * Logic when $this->search is updated.
     *
     * @param string $value
     */
    public function updatedSearch(string $value): void
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
        if ($totalItems % $this->pagination == 0) {
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
     * Delete completed todos on current page.
     *
     * @param string $todosIds
     * @param bool|null $hasMorePages
     */
    public function deleteCurrentCompletedTodos(string $todosIds, ?bool $hasMorePages): void
    {
        Todo::whereIn('id', json_decode($todosIds))
            ->delete();

        if ($this->page != 1 && ! $hasMorePages) {
            $this->gotoPage($this->page - 1);
        }
    }

    /**
     * Edit a todo.
     *
     * @param int $todoId
     * @param string $value
     */
    public function editTodo(int $todoId, string $value): void
    {
        Todo::where('id', $todoId)
            ->update([
                'todo' => $value,
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
