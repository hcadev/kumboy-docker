@section('page-title', $user->name.' - Requests')

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center border-bottom mt-3 mb-1w pb-2">
            <h4 class="my-0">Requests</h4>
            <form action="{{ route('user.search-store-request', $user->uuid) }}" METHOD="POST">
                @csrf
                <div class="input-group">
                    <input type="search" name="keyword" class="form-control form-control-sm" placeholder="Search keyword...">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                </div>
            </form>
        </div>

        @if ($requests->isEmpty())
            <div class="alert alert-danger mt-2">No records found.</div>
        @else
            @foreach ($requests AS $request)
                <p class="mb-1">
                    <span class="small text-secondary">{{ $request->created_at }}</span>
                    &#8231;
                    {{ ucwords(str_replace('_', ' ', $request->type)) }}
                    &#8231;
                    <a href="{{ route('user.store-request-details', [$request->user_uuid, $request->code]) }}">{{ $request->code }}</a>
                    &#8231;
                    <span class="small">
                        {{ ucwords($request->status) }}
                        
                        @if (in_array($request['status'], ['approved', 'rejected']) AND preg_match('/admin/i', Auth::user()->role))
                            by
                            <a href="{{ route('user.activity-log', $request['evaluated_by']) }}">{{ $request['evaluator_name'] }}</a>
                        @endif
                    </span>
                </p>
            @endforeach

            @include('shared.pagination', [
                'itemStart' => $itemStart,
                'itemEnd' => $itemEnd,
                'totalCount' => $totalCount,
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'itemsPerPage' => $itemsPerPage,
                'url' => route('user.store-requests', $request['user_uuid']),
            ])
        @endif
    </div>
</div>