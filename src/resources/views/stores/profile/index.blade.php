@extends('layouts.app')
@section('page-title', $store->name)

@section('content')
    <div class="container mt-3">
        <div class="row">
            <div class="col-12 col-md-3">
                <h4 class="mt-3">{{ $store->name }}</h4>
                <p class="small text-{{ $store->open_until === null ? 'danger' : 'success' }} mb-1">
                    @if ($store->open_until === null)
                        Closed
                    @else
                        Open until {{ date('Y-m-d', strtotime($store->open_until)) }}
                    @endif
                </p>

                @if (Auth::check() AND preg_match('/admin/i', Auth::user()->role))
                    <p class="small">
                        Owned by
                        <a href="{{ route('user.stores', $store->user_uuid) }}">{{ $store->user_name }}</a>
                    </p>
                @endif

                {{-- Web View --}}
                <ul class="nav flex-column mt-3">
                    @foreach (config('system.product_categories') AS $key => $value)
                        <li class="nav-item">
                            <a class="nav-link p-0" href="#">{{ $key }}</a>
                        </li>

                        @if (is_array($value))
                            @foreach ($value AS $subValue)
                                <li class="nav-item">
                                   <a class="nav-link p-0" href="#">&ndash; {{ $subValue }}</a>
                                </li>
                            @endforeach
                        @endif
                    @endforeach
                </ul>

                {{-- Mobile View --}}
                <div class="accordion d-md-none" id="accordionFlush">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingOne">
                            <button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                Profile Menu
                            </button>
                        </h2>
                        <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-parent="#accordionFlush">
                            <div class="accordion-body">
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link" href="#">Activity Log</a>
                                        <a class="nav-link" href="#">Activity Log</a>
                                        <a class="nav-link" href="#">Activity Log</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#">Activity Log</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#">Activity Log</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-9">
                @yield('profile_content')
            </div>
        </div>
    </div>
@endsection