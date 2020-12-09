@section('page-title', $user->name.' - Stores')

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center border-bottom mt-3 mb-1w pb-2">
            <h4 class="my-0">Stores</h4>
            @can('addStore', [new \App\Models\Store(), $user->uuid])
                <div class="text-right">
                    @can('viewStoreRequests', [new \App\Models\StoreRequest(), $user->uuid])
                        <a class="btn btn-primary btn-sm" href="{{ route('user.store-requests',  $user->uuid) }}">Requests</a>
                    @endcan
                    <a href="{{ route('user.add-store', $user->uuid) }}" class="btn btn-primary btn-sm">Add Store</a>
                </div>
            @endcan
        </div>

        @if (session('messageType'))
            <div class="alert alert-{{ session('messageType') }} mt-2">{{ session('messageContent') }}</div>
        @endif


        @if ($stores->isEmpty())
            <div class="alert alert-danger mt-2">No records found.</div>
        @else
            @foreach ($stores AS $store)
                <div class="border-bottom py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('store.products', $store->uuid) }}" class="h6">{{ $store->name }}</a>
                        <div>
                            @if ($user->uuid === Auth::user()->uuid)
                                <a href="{{ route('user.edit-store', [$user->uuid, $store->uuid]) }}" class="btn btn-primary btn-sm">Update</a>
                                <a href="{{ route('user.transfer-store', [$user->uuid, $store->uuid]) }}" class="btn btn-primary btn-sm">Transfer</a>
                            @endif
                            <a href="{{ route('store.products', $store->uuid) }}" class="btn btn-danger btn-sm">Close</a>
                        </div>
                    </div>
                    <p class="small mb-1">
                        <span class="fst-italic">Contact Number :</span>
                        <span>{{ $store->contact_number }}</span>
                    </p>
                    <p class="small mb-1">
                        <span class="fst-italic">Address :</span>
                        <span>{{ $store->address }}</span>
                    </p>
                    <p class="small mb-1">
                        <span class="fst-italic">Map Address :</span>
                        <span>{{ $store->map_address }}</span>
                    </p>
                    <p class="small mb-1">
                        <span class="fst-italic">Map Coordinates :</span>
                        <span>{{ $store->map_coordinates }}</span>
                    </p>
                    <p class="small mb-1">
                        <span class="fst-italic">Status :</span>
                        <span>
                            @if ($store->open_until !== null)
                                Open until {{ date('Y-m-d', strtotime($store->open_until)) }}
                            @else
                                Closed
                            @endif
                        </span>
                    </p>
                </div>
            @endforeach
        @endif
    </div>
</div>