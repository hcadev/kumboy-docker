@extends('layouts.app')
@section('page-title', 'Requests')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-8 offset-md-2">
                <div class="d-flex justify-content-between align-items-center border-bottom mt-3 mb-1w pb-2">
                    <h4 class="my-0">Requests</h4>
                    <form action="{{ route('request.search') }}" METHOD="POST">
                        @csrf
                        <div class="input-group">
                            <input type="search" name="keyword" class="form-control form-control-sm" placeholder="Search keyword...">
                            <button type="submit" class="btn btn-primary btn-sm">Search</button>
                        </div>
                    </form>
                </div>
                @if ($storeRequests->isEmpty())
                    <div class="alert alert-danger mt-3">No records found.</div>
                @else
                    @if ($storeRequests->isNotEmpty())
                        @foreach ($storeRequests AS $request)
                            <p class="mb-1">
                                <span class="small text-secondary">{{ $request->created_at }} &#8285;</span>
                                {{ ucfirst(str_replace('_', ' ', $request->type)) }}
                                &#8231;
                                <a href="{{ route('user.activity-log', $request->user_uuid) }}">{{ $request->user_name }}</a>
                                &#8231;
                                <a href="{{ route('user.store-request-details', [$request->user_uuid, $request->code]) }}">{{ $request->code }}</a>
                                &ndash;
                                <span class="small text-secondary">
                                        {{ ucwords($request->status) }}
                                    @if (in_array(strtolower($request->status), ['approved', 'rejected']))
                                        by
                                        <a href="{{ route('user.activity-log', $request->evaluated_by) }}">{{ $request->evaluator_name }}</a>
                                    @endif
                                </span>
                            </p>
                        @endforeach
                    @endif

                    @include('shared.pagination', [
                        'itemStart' => $itemStart,
                        'itemEnd' => $itemEnd,
                        'totalCount' => $totalCount,
                        'currentPage' => $currentPage,
                        'totalPages' => $totalPages,
                        'itemsPerPage' => $itemsPerPage,
                        'keyword' => $keyword,
                        'url' => route('request.view-all'),
                    ])
                @endif
            </div>
        </div>
    </div>
@endsection