@section('page-title', $user->name.' - Stores')

<div class="row">
    <div class="col-12 px-3">
        <h3 class="border-bottom mt-3 py-2">Stores</h3>

        @if (session('messageType'))
            <div class="alert alert-{{ session('messageType') }}">{{ session('messageContent') }}</div>
        @endif

        <a href="{{ route('user.add-store', $user->uuid) }}">Add Store</a>

        @if ($stores->isEmpty())
            <div class="alert alert-danger mt-3">No records found.</div>
        @else
            <table class="table mt-3 d-none d-md-block">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($stores AS $store)
                    <tr>
                        <td>{{ $store->name }}</td>
                        <td>{{ $store->open_until !== null ? 'Open until '.date('Y-m-d', strtotime($store->open_until)) : 'Closed.' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>