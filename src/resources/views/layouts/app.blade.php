<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Datepicker stylesheet -->
    <style rel="stylesheet">
        [type="date"] {
            background:#fff url(https://cdn1.iconfinder.com/data/icons/cc_mono_icon_set/blacks/16x16/calendar_2.png)  97% 50% no-repeat ;
        }
        [type="date"]::-webkit-inner-spin-button {
            display: none;
        }
        [type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0;
        }
    </style>

    <!-- Custom stylesheet -->
    <style rel="stylesheet">
        @media only screen and (max-width: 767px) {
            .store-logo {
                width: 133px;
                height: 133px;
            }
        }
        @media only screen and (min-width: 768px) {
            .store-logo {
                width: 133px;
                height: 133px;
            }
        }

        .material-icons { font-size: 16px; }

        .product-listing { width: 150px; }
        .product-listing:hover {
            -webkit-box-shadow: 1px 1px 2px 1px #6c757d;
            box-shadow: 1px 1px 2px 1px #6c757d;
        }

        a.card-link-wrapper {
            color: inherit;
            text-decoration: none;
        }

        .ellipsis {
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .img-preview {
            width: 150px !important;
            height: 150px !important;
        }
    </style>

    <title>@yield('page-title')</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">Kumboy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-toggled" aria-controls="navbar-toggled" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbar-toggled">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('product.view-all') }}">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('store.view-all') }}">Stores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Order Tracking</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('user.register') }}">Register</a>
                        </li>
                    @endguest

                    @auth
                        @can('viewAll', new \App\Models\User())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('user.view-all') }}">Users</a>
                            </li>
                        @endcan
                        @can('viewAllRequests', new \App\Models\StoreRequest())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('request.view-all') }}">Requests <span class="badge rounded-pill bg-primary" id="pending-request-count"></span></a>
                            </li>
                        @endcan
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('user.notifications', Auth::user()->id) }}">
                                Notifications <span class="badge rounded-pill bg-primary" id="notification-count"></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('user.activity-log', Auth::user()->id) }}">{{ Auth::user()->name }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('logout') }}">Logout</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <script type="text/javascript" src="{{ asset('js/app.js') }}"></script>

    @yield('content')

    {{-- Check for pending requests and notifications every 5 seconds --}}
    <script>
        var role = '{{ Auth::check() ? Auth::user()->role : '' }}';
        var logged_in = '{{ Auth::check() }}';
        count();

        setInterval(function () {
            count();
        }, 5000);

        function count() {
            if (role.match('admin')) {
                axios.get('{{ route('request.count-pending') }}')
                    .then(function (response) {
                        var pending = parseInt(response.data);
                        document.getElementById('pending-request-count').innerText = pending > 0 ? pending : '';
                    });
            }

            if (logged_in) {
                axios.get('{{ route('notification.count-unread') }}')
                    .then(function (response) {
                        var unread = parseInt(response.data);
                        document.getElementById('notification-count').innerText = unread > 0 ? unread : '';
                    });
            }
        }
    </script>
</body>
</html>