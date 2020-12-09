<p class="mb-1">
    <span class="fw-bold">Store Name:</span>
    <span>{{ $storeApplication['name'] }}</span>
</p>
<p class="mb-1">
    <span class="fw-bold">Contact Number:</span>
    <span>{{ $storeApplication['contact_number'] }}</span>
</p>
<p class="mb-1">
    <span class="fw-bold">Address:</span>
    <span>{{ $storeApplication['address'] }}</span>
</p>

<div style="width: 100%; height: 300px;" id="map"></div>

<p class="mb-1 mt-2">
    <span class="fw-bold">Map Address:</span>
    <span>{{ $storeApplication['map_address'] }}</span>
</p>
<p class="mb-1">
    <span class="fw-bold">Map Coordinates:</span>
    <span>{{ $storeApplication['map_coordinates'] }}</span>
</p>
<p class="mb-1">
    <span class="fw-bold">Attachment:</span>
    <a href="#" data-toggle="modal" data-target="#attachment-modal">View Attachment</a>
</p>
<p class="mb-3">
    <span class="fw-bold">Open Until:</span>
    <span>{{ date('Y-m-d', strtotime($storeApplication['open_until'])) }}</span>
</p>

<script>
    function initMap() {
        var coordinates = "{{ $storeApplication['map_coordinates'] }}";
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