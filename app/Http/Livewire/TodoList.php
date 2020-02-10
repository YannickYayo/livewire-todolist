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

    /**
     * Render the component.
     *
     * @return View
     */
    public function render(): View
    {
        $todos = Todo::paginate(self::PAGINATION);

        return view('livewire.todo-list', compact('todos'));
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
     * @param int|null $countTodosOnCurrentPage
     */
    public function deleteTodo(int $todoId, ?int $countTodosOnCurrentPage): void
    {
        Todo::destroy($todoId);

        if (! is_null($countTodosOnCurrentPage) && $countTodosOnCurrentPage == 1) {
            $this->gotoPage($this->page - 1);
        }
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
