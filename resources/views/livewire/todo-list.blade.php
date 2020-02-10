<div>
    <h1 class="text-6xl font-bold leading-tight text-center text-white">Todo List</h1>

    <section id="todolist" class="relative block w-full mx-auto mt-8 shadow-lg lg:w-2/3 xl:w-1/2">
        <div class="block w-full h-16 bg-indigo-500 rounded-t-lg"></div>

        <header id="header" class="border-b border-indigo-600">
            <div x-data class="flex flex-row items-stretch bg-white">
                <div class="flex items-center bg-gray-100">
                    <svg class="w-8 h-8 mx-4 text-gray-500 cursor-pointer fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M4.516 7.548c.436-.446 1.043-.481 1.576 0L10 11.295l3.908-3.747c.533-.481 1.141-.446 1.574 0 .436.445.408 1.197 0 1.615-.406.418-4.695 4.502-4.695 4.502a1.095 1.095 0 01-1.576 0S4.924 9.581 4.516 9.163c-.409-.418-.436-1.17 0-1.615z"/>
                    </svg>
                </div>
                <input x-on:keydown.enter="$event.target.value = ''" wire:keydown.enter="addTodo($event.target.value, {{ $todos->lastPage() }}, {{ $todos->total() }})" class="flex-grow block pt-4 pb-4 pl-2 text-2xl italic font-normal text-gray-700 border-l-2 border-indigo-600 outline-none" type="text" name="new-todo" id="new-todo" placeholder="Whats need to be done ?" autofocus>
            </div>
        </header>

        <section id="main" class="relative">
            <ul id="todo-list" class="list-none">
                @foreach ($todos as $todo)
                    <li class="relative border-b border-indigo-600 last:border-b-0">
                        <div class="flex flex-row items-stretch bg-white">
                            <div class="flex items-center bg-gray-100">
                                <svg class="@if($todo->isCompleted()) text-green-500 @endif w-8 h-8 mx-4 cursor-pointer fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" >
                                    <path d="M8.294 16.998c-.435 0-.847-.203-1.111-.553L3.61 11.724a1.392 1.392 0 01.27-1.951 1.392 1.392 0 011.953.27l2.351 3.104 5.911-9.492a1.396 1.396 0 011.921-.445c.653.406.854 1.266.446 1.92L9.478 16.34a1.39 1.39 0 01-1.12.656c-.022.002-.042.002-.064.002z"/>
                                </svg>
                            </div>

                            <p class="@if($todo->isCompleted()) line-through @endif flex-1 pt-4 pb-4 pl-2 text-2xl font-normal text-gray-700 break-words border-l-2 border-indigo-600">{{ $todo->todo }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    </section>

    {{ $todos->links() }}
</div>
