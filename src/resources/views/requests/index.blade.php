@extends('layouts.app')
@section('page-title', 'Requests')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 px-3">
                <div class="d-flex justify-content-between border-bottom mt-3 pb-2">
                    <h3>Requests</h3>
                    <form action="{{ route('request.search') }}" METHOD="POST">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="keyword" class="form-control" placeholder="reference #...">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </form>
                </div>
                @if ($userRequests->isEmpty())
                    <div class="alert alert-danger mt-3">No records found.</div>
                @else
                    <div class="text-secondary mb-2">
                        {{ 'Displaying '.$itemStart.'-'.$itemEnd.' of '.$totalCount.' with keyword '.$keyword.'.' }}
                    </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Reference #</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if ($userRequests->isNotEmpty())
                                    @foreach ($userRequests AS $request)
                                    <tr>
                                        <td>{{ $request->created_at }}</td>
                                        <td><a href="{{ route('user.request-details', [$request->user_uuid, $request->code]) }}">{{ $request->code }}</a></td>
                                        <td><a href="{{ route('user.activity-log', [$request->user_uuid, 1, 25]) }}">{{ $request->user_name }}</a></td>
                                        <td>{{ ucwords($request->type) }}</td>
                                        <td>
                                            @if (in_array(strtolower($request->status), ['pending', 'cancelled']))
                                                {{ ucwords($request->status) }}
                                            @else
                                                {{ ucwords($request->status) }}
                                                by
                                                <a href="{{ route('user.activity-log', [$request->evaluated_by, 1, 25]) }}">{{ $request->evaluator_name }}</a>
                                            @endif
                                        </td>
                                        <td></td>
                                    </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>

                    @includeWhen($totalPages > 1, 'shared.pagination', [
                        'currentPage' => $currentPage,
                        'totalPages' => $totalPages,
                        'itemsPerPage' => $itemsPerPage,
                        'keyword' => $keyword,
                        'url' => 'requests',
                    ])
                @endif
            </div>
        </div>
    </div>
@endsection