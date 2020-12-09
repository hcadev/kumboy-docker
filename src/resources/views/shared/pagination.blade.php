<nav class="my-3 d-flex justify-content-between align-items-center">
    <p class="small text-secondary">
        {{ $itemStart.'-'.$itemEnd.' of '.$totalCount }}
    </p>
    @if ($totalPages > 1)
        <ul class="pagination justify-content-center">
            @php
                $keyword = empty($keyword) ? null : $keyword;
            @endphp
            @if ($currentPage - 1 > 1)
                <li class="page-item">
                    <a class="page-link" href="{{ url($url.'/1/'.$itemsPerPage.'/'.$keyword) }}">First</a>
                </li>
            @endif

            @if ($currentPage > 1)
                <li class="page-item">
                    <a class="page-link" href="{{ url($url.'/'.($currentPage - 1).'/'.$itemsPerPage.'/'.$keyword) }}">&lt;</a>
                </li>
            @endif

            @if ($currentPage - 1 > 0)
                <li class="page-item">
                    <a class="page-link" href="{{ url($url.'/'.($currentPage - 1).'/'.$itemsPerPage.'/'.$keyword) }}">{{ $currentPage - 1 }}</a>
                </li>
            @endif

            <li class="page-item disabled">
                <a class="page-link" href="#">{{ $currentPage }}</a>
            </li>

            @if ($currentPage + 1 <= $totalPages)
                <li class="page-item">
                    <a class="page-link" href="{{ url($url.'/'.($currentPage + 1).'/'.$itemsPerPage.'/'.$keyword) }}">{{ $currentPage + 1 }}</a>
                </li>
            @endif

            @if ($currentPage < $totalPages)
                <li class="page-item">
                    <a class="page-link" href="{{ url($url.'/'.($currentPage + 1).'/'.$itemsPerPage.'/'.$keyword) }}">&gt;</a>
                </li>
            @endif

            @if ($currentPage + 1 < $totalPages)
                <li class="page-item">
                    <a class="page-link" href="{{ url($url.'/'.$totalPages.'/'.$itemsPerPage.'/'.$keyword) }}">Last</a>
                </li>
            @endif
        </ul>
    @endif
</nav>