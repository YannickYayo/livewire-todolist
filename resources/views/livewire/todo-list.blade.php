<div>
    <h1 class="text-6xl font-bold leading-tight text-center text-white">Todo List</h1>

    <input
        class="block w-full p-4 mx-auto mt-8 text-2xl font-normal text-gray-700 rounded-lg shadow-lg outline-none lg:w-2/3 xl:w-1/2"
        autocomplete="false" type="search" name="search" id="search" wire:model.debounce.700ms="search"
        placeholder="Search">

    <section id="todolist" class="relative block w-full mx-auto mt-8 shadow-lg lg:w-2/3 xl:w-1/2">
        <div class="block w-full h-16 bg-indigo-500 rounded-t-lg"></div>

        <header id="header" class="border-b border-indigo-600">
            <div x-data class="flex flex-row items-stretch bg-white">
                <div class="flex items-center bg-gray-100">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        wire:click="updateTodosStatusOnCurrentPage('{{ json_encode(collect($todos->items())->map->id->toArray()) }}')"
                        class="w-8 h-8 mx-4 cursor-pointer @if($checkItemsOnCurrentPage) text-gray-800 @else text-gray-500 @endif">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>

                <input x-on:keydown.enter="$event.target.value = ''"
                    wire:keydown.enter="addTodo($event.target.value, {{ $todos->lastPage() }}, {{ $todos->total() }})"
                    class="flex-grow block pt-4 pb-4 pl-2 text-2xl italic font-normal text-gray-700 border-l-2 border-indigo-600 outline-none"
                    type="text" name="new-todo" id="new-todo" placeholder="Whats need to be done ?" autofocus>
            </div>
        </header>

        <section id="main" class="relative">
            <ul id="todo-list" class="list-none">
                @foreach ($todos as $todo)
                <li wire:key="li-{{ $todo->id }}" x-data="initTodo({{ json_encode($todo) }})"
                    x-on:mouseover="show_destroy = true" x-on:mouseleave="show_destroy = false"
                    class="relative border-b border-indigo-600 last:border-b-0">
                    <div class="flex flex-row items-stretch bg-white">
                        <div class="flex items-center bg-gray-100">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                wire:click="updateTodoStatus({{ $todo->id }}, '{{ $todo->status }}')"
                                class="@if($todo->isCompleted()) text-green-500 @endif w-8 h-8 mx-4 cursor-pointer">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>

                        <p x-show="!edit" x-on:dblclick="editing($refs.edit_todo_{!! $todo->id !!})"
                            class="@if($todo->isCompleted()) line-through @endif flex-1 pt-4 pb-4 pl-2 text-2xl font-normal text-gray-700 break-words border-l-2 border-indigo-600">
                            {{ $todo->todo }}
                        </p>
                        <input x-on:click.away="leaveEditing()" x-show="edit" x-ref="edit_todo_{!! $todo->id !!}"
                            wire:keydown.enter="editTodo({{ $todo->id }}, $event.target.value)" type="text"
                            class="flex-1 pt-4 pb-4 pl-2 text-2xl font-normal text-gray-700 break-words border-l-2 border-indigo-600"
                            name="edit-todo-{{ $todo->id }}" id="edit-todo-{{ $todo->id }}" x-model="todo">

                        <div class="flex items-center">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                class="absolute right-0 w-8 h-8 mr-4 text-gray-800 transition duration-300 ease-out transform cursor-pointer hover:scale-110 hover:text-red-500"
                                x-cloak x-show="show_destroy && !edit"
                                wire:click="deleteTodo({{ $todo->id }}, {{ $todos->count() }})">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </section>

        <section id="footer" class="flex flex-row items-center block p-4 mx-auto bg-gray-300 rounded-b-lg shadow-lg">
            <span class="pr-4">{{ $todos->count() }} / {{ $todos->total() }} items </span>

            <ul class="flex flex-row items-center justify-around flex-grow">
                <li wire:click="$set('filter', 'all')" class="cursor-pointer @if($filter == 'all') font-bold @endif">
                    All
                </li>
                <li wire:click="$set('filter', 'active')"
                    class="cursor-pointer @if($filter == 'active') font-bold @endif">
                    Active
                </li>
                <li wire:click="$set('filter', 'completed')"
                    class="cursor-pointer @if($filter == 'completed') font-bold @endif">
                    Completed
                </li>
                <li>
                    <button
                        wire:click="deleteCurrentCompletedTodos('{{ json_encode(collect($todos->items())->map->id->toArray()) }}', {{ $todos->hasMorePages() }})"
                        class="@if(!$checkItemsOnCurrentPage) invisible @endif px-4 py-2 text-white bg-indigo-500 rounded-lg hover:bg-indigo-400">
                        Clear completed
                    </button>
                </li>
            </ul>
        </section>
    </section>

    {{ $todos->links() }}
</div>