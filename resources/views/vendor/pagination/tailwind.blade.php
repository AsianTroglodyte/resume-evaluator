@if ($paginator->hasPages())
    <nav
        role="navigation"
        aria-label="{{ __('Pagination Navigation') }}"
        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
    >
        <p class="text-sm text-base-content/70">
            {!! __('Showing') !!}
            @if ($paginator->firstItem())
                <span class="font-medium text-base-content">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="font-medium text-base-content">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            {!! __('of') !!}
            <span class="font-medium text-base-content">{{ $paginator->total() }}</span>
            {!! __('results') !!}
        </p>

        <div class="join self-start sm:self-auto">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="btn btn-sm join-item btn-disabled">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}" class="btn btn-sm join-item">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true" class="btn btn-sm join-item btn-disabled">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span aria-current="page" class="btn btn-sm join-item btn-primary">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}" class="btn btn-sm join-item">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}" class="btn btn-sm join-item">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="btn btn-sm join-item btn-disabled">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>
    </nav>
@endif
