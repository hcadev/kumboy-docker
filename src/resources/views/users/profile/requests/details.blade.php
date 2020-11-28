@section('page-title', $user->name.' - Request Details')

<div class="row">
    <div class="col-12 px-3">
        <h3 class="border-bottom mt-3 pb-2">Request Details</h3>

        @if (session('messageType'))
            <div class="alert alert-{{ session('messageType') }}">{{ session('messageContent') }}</div>
        @endif

        <p class="mb-1">
            <span class="fw-bold">Ref#:</span>
            <span>{{ $request['code'] }}</span>
        </p>
        <p class="mb-1">
            <span class="fw-bold">Date:</span>
            <span>{{ $request['created_at'] }}</span>
        </p>
        <p class="mb-1">
            <span class="fw-bold">Type:</span>
            <span>{{ ucwords($request['type']) }}</span>
        </p>
        <p class="mb-3">
            <span class="fw-bold">Status:</span>
            <span>{{ ucwords($request['status']) }}</span>
        </p>

        @if(strtolower($request['type']) === 'store application')
            <p class="mb-1">
                <span class="fw-bold">Store Name:</span>
                <span>{{ ucwords($request['store_application']['name']) }}</span>
            </p>
            <p class="mb-1">
                <span class="fw-bold">Contact Number:</span>
                <span>{{ ucwords($request['store_application']['contact_number']) }}</span>
            </p>
            <p class="mb-1">
                <span class="fw-bold">Address:</span>
                <span>{{ ucwords($request['store_application']['address']) }}</span>
            </p>

            <div style="width: 100%; height: 300px;" id="map"></div>

            <p class="mb-1">
                <span class="fw-bold">Map Address:</span>
                <span>{{ ucwords($request['store_application']['map_address']) }}</span>
            </p>
            <p class="mb-1">
                <span class="fw-bold">Map Coordinates:</span>
                <span>{{ ucwords($request['store_application']['map_coordinates']) }}</span>
            </p>
            <p class="mb-1">
                <span class="fw-bold">Attachment:</span>
                <a href="#" data-toggle="modal" data-target="#attachment-modal">View Attachment</a>
            </p>
            <p class="mb-3">
                <span class="fw-bold">Open Until:</span>
                <span>{{ ucwords($request['store_application']['open_until']) }}</span>
            </p>
        @endif

        <div class="mb-3 d-flex justify-content-between">
            @if (Auth::user()->uuid === $request['user_uuid'] AND strtolower($request['status']) === 'pending')
                <a href="#" class="btn btn-danger" data-toggle="modal" data-target="#cancel-dialog">Cancel Request</a>
            @elseif (in_array(strtolower(Auth::user()->role), ['superadmin', 'admin']) AND strtolower($request['status']) === 'pending')
                <a href="#" class="btn btn-danger" data-toggle="modal" data-target="#reject-dialog">Reject Request</a>
                <a href="#" class="btn btn-success" data-toggle="modal" data-target="#approve-dialog">Approve Request</a>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="attachment-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="attachment-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attachment-modal-label">Attachment</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <embed src="{{ asset('storage/attachments/'.$request['store_application']['attachment']) }}" frameborder="0" width="100%" height="400px">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cancel-dialog" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h5>Are you sure you want to cancel this request?</h5>
                <form method="POST" action="{{ route('user.cancel-request', [$user->uuid, $request['code']]) }}">
                    @csrf
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Go Back</button>
                        <button type="submit" class="btn btn-danger btn-sm">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reject-dialog" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h5>Are you sure you want to reject this request?</h5>
                <form method="POST" action="{{ route('user.reject-request', [$user->uuid, $request['code']]) }}">
                    @csrf
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Go Back</button>
                        <button type="submit" class="btn btn-danger btn-sm">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="approve-dialog" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h5>Are you sure you want to approve this request?</h5>
                <form method="POST" action="{{ route('user.approve-request', [$user->uuid, $request['code']]) }}">
                    @csrf
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Go Back</button>
                        <button type="submit" class="btn btn-success btn-sm">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function initMap() {
        var coordinates = "{{ $request['store_application']['map_coordinates'] }}";
        coordinates = coordinates.split(',');

        lat = parseFloat(coordinates[0]);
        lng = parseFloat(coordinates[1]);

        const address = { lat: lat, lng: lng };
        const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 18,
            center: address,
            mapTypeId: google.maps.MapTypeId.HYBRID,
        });

        const marker = new google.maps.Marker({
                position: { lat: lat, lng: lng },
                map,
            });
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GMAP_API_KEY') }}&callback=initMap" defer></script>