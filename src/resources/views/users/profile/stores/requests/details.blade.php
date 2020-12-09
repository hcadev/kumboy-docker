@section('page-title', $user->name.' - Request Details')

<div class="row">
    <div class="col-12">
        <h4 class="border-bottom mt-3 pb-2">Request Details</h4>

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
            <span>{{ ucwords(str_replace('_', ' ', $request['type'])) }}</span>
        </p>
        <p class="mb-3">
            <span class="fw-bold">Status:</span>
            <span>
                {{ ucwords($request['status']) }}

                @if (in_array($request['status'], ['approved', 'rejected']) AND preg_match('/admin/i', Auth::user()->role))
                    by
                    <a href="{{ route('user.activity-log', $request['evaluated_by']) }}">{{ $request['evaluator_name'] }}</a>
                @endif
            </span>
        </p>

        @includeWhen(in_array($request['type'], ['store creation', 'store update']), 'users.profile.stores.requests.application.details', [
            'storeApplication' => $request['store_application'],
        ])

        @includeWhen($request['type'] === 'store transfer', 'users.profile.stores.requests.transfer.details', [
            'storeTransfer' => $request['store_transfer'],
            'store' => $request['store'],
        ])

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
                @if (in_array($request['type'], ['store creation', 'store update']))
                    <embed src="{{ asset('storage/attachments/'.$request['store_application']['attachment']) }}" frameborder="0" width="100%" height="400px">
                @elseif ($request['type'] === 'store transfer')
                    <embed src="{{ asset('storage/attachments/'.$request['store_transfer']['attachment']) }}" frameborder="0" width="100%" height="400px">
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cancel-dialog" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h5>Are you sure you want to cancel this request?</h5>
                <form method="POST" action="{{ route('user.cancel-store-request', [$user->uuid, $request['code']]) }}">
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
                <form method="POST" action="{{ route('user.reject-store-request', [$user->uuid, $request['code']]) }}">
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
                <form method="POST" action="{{ route('user.approve-store-request', [$user->uuid, $request['code']]) }}">
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