@if ($paginator->hasPages())
    <nav class="flex flex-wrap items-center justify-center gap-2" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="inline-flex h-10 items-center rounded-[10px] border border-[#efe8dc] bg-white px-3 text-sm text-[#b0aaa4]">Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-10 items-center rounded-[10px] border border-[#efe8dc] bg-white px-3 text-sm font-medium text-[#1a1a1a] hover:border-[#f5b400]">Previous</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-1 text-sm text-[#8a8680]">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="inline-flex h-10 min-w-10 items-center justify-center rounded-[10px] bg-[#f5b400] px-3 text-sm font-semibold text-[#1a1a1a]">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="inline-flex h-10 min-w-10 items-center justify-center rounded-[10px] border border-[#efe8dc] bg-white px-3 text-sm font-medium text-[#1a1a1a] hover:border-[#f5b400]" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-10 items-center rounded-[10px] border border-[#efe8dc] bg-white px-3 text-sm font-medium text-[#1a1a1a] hover:border-[#f5b400]">Next</a>
        @else
            <span class="inline-flex h-10 items-center rounded-[10px] border border-[#efe8dc] bg-white px-3 text-sm text-[#b0aaa4]">Next</span>
        @endif
    </nav>
@endif
