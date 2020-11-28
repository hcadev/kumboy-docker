<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-CuOF+2SnTUfTwSZjCXf01h7uYhfOBuxIhGKPbfEJ3+FqH/s6cIFN9bGr1HmAg4fQ" crossorigin="anonymous">

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

    {{-- Bootstrap 5 JS bundle, load it here first for components to work on sub-views. --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-popRpmFF9JQgExhfw5tZT4I9/CI5e2QcuUZPOVXb1m7qUmeR2b50u+YFEYe1wgzy" crossorigin="anonymous"></script>

    <title>@yield('page-title')</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-secondary sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">Kumboy</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-toggled" aria-controls="navbar-toggled" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbar-toggled">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Categories</a>
                    </li>
                </ul>
                <form class="d-flex">
                    <input class="form-control mr-2" type="search" placeholder="Search" aria-label="Search">
                </form>
                <ul class="navbar-nav ml-auto">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('user.register') }}">Register</a>
                        </li>
                    @endguest

                    @auth
                        @can('viewAllUsers', new \App\Models\User())
                            <li class="nav-item">
                                <a class="nav-link" href="#">Users</a>
                            </li>
                        @endcan
                        @can('viewAllRequests', new \App\Models\UserRequest())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('request.view-all', [1, 25]) }}">Requests <span class="badge rounded-pill bg-primary" id="pending-request-count"></span></a>
                            </li>
                        @endcan
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('user.notifications', [Auth::user()->uuid, 1, 25]) }}">Notifications <span class="badge rounded-pill bg-primary" id="notification-count"></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('user.activity-log', [Auth::user()->uuid, 1, 25]) }}">{{ Auth::user()->name }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('logout') }}">Logout</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    @yield('content')

{{-- Axios --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.0/axios.min.js" integrity="sha512-DZqqY3PiOvTP9HkjIWgjO6ouCbq+dxqWoJZ/Q+zPYNHmlnI2dQnbJ5bxAHpAMw+LXRm4D72EIRXzvcHQtE8/VQ==" crossorigin="anonymous"></script>

{{-- Check for pending requests and notifications every 5 seconds --}}
<script>
    var role = '{{ Auth::check() ? Auth::user()->role : '' }}';
    count();

    setInterval(function () {
        count();
    }, 5000);

    function count() {
        if (role.match('/admin/i')) {
            axios.get('{{ route('request.count-pending') }}')
                .then(function (response) {
                    var pending = parseInt(response.data);
                    document.getElementById('pending-request-count').innerText = pending > 0 ? pending : '';
                });
        }

        axios.get('{{ route('notification.count-unread') }}')
            .then(function (response) {
                var unread = parseInt(response.data);
                document.getElementById('notification-count').innerText = unread > 0 ? unread : '';
            });
    }
</script>
</body>
</html>