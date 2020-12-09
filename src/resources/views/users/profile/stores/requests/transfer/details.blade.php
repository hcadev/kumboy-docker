<p class="mb-1">
    <span class="fw-bold">Store Name:</span>
    <span>{{ $store['name'] }}</span>
</p>
<p class="mb-1">
    <span class="fw-bold">Contact Number:</span>
    <span>{{ $store['contact_number'] }}</span>
</p>
<p class="mb-1">
    <span class="fw-bold">Address:</span>
    <span>{{ $store['address'] }}</span>
</p>

<p class="mb-1 mt-2">
    <span class="fw-bold">Map Address:</span>
    <span>{{ $store['map_address'] }}</span>
</p>
<p class="mb-1">
    <span class="fw-bold">Map Coordinates:</span>
    <span>{{ $store['map_coordinates'] }}</span>
</p>
<p class="mb-1">
    <span class="fw-bold">Attachment:</span>
    <a href="#" data-toggle="modal" data-target="#attachment-modal">View Attachment</a>
</p>
<p class="mb-1">
    <span class="fw-bold">Open Until:</span>
    <span>{{ date('Y-m-d', strtotime($store['open_until'])) }}</span>
</p>

<p class="mb-3">
    <span class="fw-bold">Transfer To:</span>
    <span>
        <a href="{{ route('user.activity-log', $storeTransfer['target_uuid']) }}">{{ $storeTransfer['target_name'] }}</a>
    </span>
</p>