@extends('layouts.app')

@section('content')
    @if(empty($user))
        @include('users.404')
    @else
        <div class="container mt-3">
            <div class="row">
                <div class="col-12 col-md-4">
                    <h3 class="text-center text-{{ $user->banned_until === null ? 'success' : 'danger' }} mt-3">{{ $user->name }}</h3>

                    {{-- Web View --}}
                    <ul class="nav flex-column d-none d-md-block text-center">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('user.activity-log', [$user->uuid, 1, 25]) }}">Activity Log</a>
                        </li>
                        @can('viewAccountSettings', $user)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('user.account-settings', $user->uuid) }}">Account Settings</a>
                            </li>
                        @endcan
                        @can('viewAddressBook', [new \App\Models\UserAddressBook(), $user->uuid])
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('user.address-book',  $user->uuid) }}">Address Book</a>
                            </li>
                        @endcan
                        @can('viewUserStores', [new \App\Models\Store(), $user->uuid])
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('user.stores',  $user->uuid) }}">Stores</a>
                            </li>
                        @endcan
                        @can('viewUserRequests', [new \App\Models\UserRequest(), $user->uuid])
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('user.requests',  [$user->uuid, 1, 25]) }}">Requests</a>
                            </li>
                        @endcan
                    </ul>

                    <div class="accordion d-md-none" id="accordionFlush">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-headingOne">
                                <button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                    Profile Menu
                                </button>
                            </h2>
                            <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-parent="#accordionFlush">
                                <div class="accordion-body">
                                    <ul class="nav flex-column text-center">
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('user.activity-log', [$user->uuid, 1, 25]) }}">Activity Log</a>
                                        </li>
                                        @can('viewAccountSettings', $user)
                                            <li class="nav-item">
                                                <a class="nav-link" href="{{ route('user.account-settings', $user->uuid) }}">Account Settings</a>
                                            </li>
                                        @endcan
                                        @can('viewAddressBook', [new \App\Models\UserAddressBook(), $user->uuid])
                                            <li class="nav-item">
                                                <a class="nav-link" href="{{ route('user.address-book', $user->uuid) }}">Address Book</a>
                                            </li>
                                        @endcan
                                        @can('viewUserStores', [new \App\Models\Store(), $user->uuid])
                                            <li class="nav-item">
                                                <a class="nav-link" href="{{ route('user.stores',  $user->uuid) }}">Stores</a>
                                            </li>
                                        @endcan
                                        @can('viewUserRequests', [new \App\Models\UserRequest(), $user->uuid])
                                            <li class="nav-item">
                                                <a class="nav-link" href="{{ route('user.requests',  [$user->uuid, 1, 25]) }}">Requests</a>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-8">
                    @include($content, $contentData)
                </div>
            </div>
        </div>
    @endif
@endsection