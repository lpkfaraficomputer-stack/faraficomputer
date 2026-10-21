@php($paginator = $paginator ?? null)

@if($paginator && $paginator->lastPage() > 1)
    <div class="pagination">
        <a href="{{ $paginator->previousPageUrl() ?: '#' }}" class="page-link {{ $paginator->onFirstPage() ? 'disabled' : '' }}">‹</a>
        @for($i = 1; $i <= $paginator->lastPage(); $i++)
            @if($i === 1 || $i === $paginator->lastPage() || abs($i - $paginator->currentPage()) <= 1)
                <a href="{{ $paginator->url($i) }}" class="page-link {{ $paginator->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
            @elseif(abs($i - $paginator->currentPage()) === 2)
                <span class="page-link page-dots">…</span>
            @endif
        @endfor
        <a href="{{ $paginator->nextPageUrl() ?: '#' }}" class="page-link {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">›</a>
    </div>
@endif
