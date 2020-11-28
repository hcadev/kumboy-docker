@section('page-title', $user->name.' - Requests')

<div class="row">
    <div class="col-12 px-3">
        <h3 class="border-bottom mt-3 pb-2">Requests</h3>
        @if ($requests->isEmpty())
            <div class="alert alert-danger">No records found.</div>
        @else
            <div class="text-secondary mb-2">
                {{ 'Displaying '.$itemStart.'-'.$itemEnd.' of '.$totalCount.'.' }}
            </div>

            @foreach ($requests AS $request)
                <div class="mt-3">
                    {{ $request->created_at }}
                    - Ref# <a href="{{ route('user.request-details', [$user->uuid, $request->code]) }}">{{ $request->code }}</a>
                    - {{ $request->type }}
                    - {{ $request->status }}
                </div>
            @endforeach

            @includeWhen($totalPages > 1, 'shared.pagination', [
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'itemsPerPage' => $itemsPerPage,
                'url' => 'users/'.$user->uuid,
            ])
        @endif
    </div>
</div>