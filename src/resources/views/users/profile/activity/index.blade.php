@section('page-title', $user->name.' - Activity Log')

<div class="row">
    <div class="col-12 px-3">
        <h3 class="border-bottom mt-3 pb-2">Activity Log</h3>
        @if ($activities->isEmpty())
            <div class="alert alert-danger">No records found.</div>
        @else
            <div class="text-secondary mb-2">
                {{ 'Displaying '.$itemStart.'-'.$itemEnd.' of '.$totalCount.'.' }}
            </div>

            @foreach ($activities AS $activity)
                <div>{{ $activity->date_recorded.' - '.str_replace('\n', '<br>', $activity->action_taken) }}</div>
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