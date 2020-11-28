@section('page-title', $user->name.' - Address Book')

<div class="row">
    <div class="col-12 px-3">
        <h3 class="border-bottom mt-3 pb-2">Address Book</h3>

        @if (session('messageType'))
            <div class="alert alert-{{ session('messageType') }}">{{ session('messageContent') }}</div>
        @endif

        <a href="{{ route('user.add-address', $user->uuid) }}">Add Address</a>

        @if ($addressBook->isEmpty())
            <div class="alert alert-danger mt-3">No records found.</div>
        @else
            <div class="row row-cols-1 row-cols-md-2">
                @foreach ($addressBook AS $address)
                    <div class="col mt-3">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title">{{ $address->label }}</h4>
                                <div class="btn-group p-1">
                                    <a href="{{ route('user.edit-address', [$user->uuid, $address->id]) }}" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="{{ route('user.delete-address', [$user->uuid, $address->id]) }}" class="btn btn-danger btn-sm">Delete</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="mb-1">
                                    <span class="fw-bold">Label:</span>
                                    <span>{{ $address['label'] }}</span>
                                </p>
                                <p class="mb-1">
                                    <span class="fw-bold">Contact Person:</span>
                                    <span>{{ $address['contact_person'] }}</span>
                                </p>
                                <p class="mb-1">
                                    <span class="fw-bold">Contact Number:</span>
                                    <span>{{ $address['contact_number'] }}</span>
                                </p>
                                <p class="mb-1">
                                    <span class="fw-bold">Address:</span>
                                    <span>{{ $address['address'] }}</span>
                                </p>
                                <p class="mb-1">
                                    <span class="fw-bold">Map Coordinates:</span>
                                    <span>{{ $address['map_coordinates'] }}</span>
                                </p>
                                <p class="mb-1">
                                    <span class="fw-bold">Map Address:</span>
                                    <span>{{ $address['map_address'] }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>