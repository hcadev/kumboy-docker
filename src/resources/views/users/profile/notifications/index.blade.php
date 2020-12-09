@section('page-title', $user->name.' - Notifications')

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center border-bottom mt-3 mb-1w pb-2">
            <h4 class="my-0">Notifications</h4>
            <form action="{{ route('user.search-notification', $user->uuid) }}" METHOD="POST">
                @csrf
                <div class="input-group">
                    <input type="search" name="keyword" class="form-control form-control-sm" placeholder="Search keyword...">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                </div>
            </form>
        </div>
        @if ($notifications->isEmpty())
            <div class="alert alert-danger">No records found.</div>
        @else
            <div class="text-secondary mb-2">
{{--                {{ 'Displaying '.$itemStart.'-'.$itemEnd.' of '.$totalCount.'.' }}--}}
            </div>

            @if ($notifications->whereNull('read_at')->count() > 0)
                <div class="bg-light px-2">
                    @foreach ($notifications AS $notification)
                        @if ($notification->read_at === null)
                            <p class="mb-1">
                                <span class="small text-secondary">{{ date('Y-m-d H:i:s', strtotime($notification->created_at)) }}</span>
                                &#8285;
                                {{ ucfirst($notification->data['message']) }}
                                @if ($notification->data['type'] === 'store_request')
                                    <a class="small" href="{{ route('user.read-notification', [Auth::user()->uuid, $notification->id]) }}">{{ $notification->data['code'] }}</a>
                                @elseif ($notification->data['type'] === 'store_received')
                                    <a class="small" href="{{ route('user.read-notification', [Auth::user()->uuid, $notification->id]) }}">View Store</a>
                                @endif
                            </p>
                        @else
                            @break;
                        @endif
                    @endforeach
                </div>
            @endif
            @if ($notifications->whereNotNull('read_at')->count() > 0)
                <div class="px-2">
                    @foreach ($notifications AS $notification)
                        @if ($notification->read_at !== null)
                            <p class="mb-1">
                                <span class="small text-secondary">{{ date('Y-m-d H:i:s', strtotime($notification->created_at)) }}</span>
                                &#8285;
                                {{ ucfirst($notification->data['message']) }}
                                <a class="small" href="{{ route('user.view-notification', [Auth::user()->uuid, $notification->id]) }}">{{ $notification->data['code'] }}</a>
                            </p>
                        @endif
                    @endforeach
                </div>
            @endif

            @include('shared.pagination', [
                'itemStart' => $itemStart,
                'itemEnd' => $itemEnd,
                'totalCount' => $totalCount,
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'itemsPerPage' => $itemsPerPage,
                'url' => route('user.notifications', $user->uuid),
            ])
        @endif
    </div>
</div>