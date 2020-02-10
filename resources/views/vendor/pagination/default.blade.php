@if ($paginator->hasPages())
    <div class="flex items-center justify-center w-full mx-auto mt-8 lg:w-2/3 xl:w-1/2">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="px-4 py-3 mr-1 text-xl font-normal text-white no-underline bg-indigo-500 rounded cursor-not-allowed">&laquo;</span>
        @else
            <a
                class="px-4 py-3 mr-1 text-xl font-normal text-white no-underline bg-indigo-500 rounded cursor-pointer hover:bg-indigo-400"
                wire:click="previousPage"
                rel="prev"
            >
                &laquo;
            </a>
        @endif
    
        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="px-4 py-3 mx-1 text-xl font-normal text-white no-underline bg-indigo-500 cursor-not-allowed">{{ $element }}</span>
            @endif
    
            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-4 py-3 mx-1 text-xl font-normal text-white no-underline bg-indigo-600 rounded cursor-pointer">{{ $page }}</span>
                    @else
                        <a class="px-4 py-3 mx-1 text-xl font-normal text-white no-underline bg-indigo-500 rounded cursor-pointer hover:bg-indigo-400" wire:click="gotoPage({{ $page }})">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
    
        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a class="px-4 py-3 ml-1 text-xl font-normal text-white no-underline bg-indigo-500 rounded cursor-pointer hover:bg-indigo-400" wire:click="nextPage" rel="next">&raquo;</a>
        @else
            <span class="px-4 py-3 ml-1 text-xl font-normal text-white no-underline bg-indigo-500 rounded cursor-not-allowed hover:bg-indigo-400">&raquo;</span>
        @endif
    </div>
@endif