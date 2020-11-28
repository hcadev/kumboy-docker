@extends('layouts.clean')
@section('page-title', 'Login')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card mt-5">
                    <div class="card-title">
                        <h3 class="text-center py-3">Login</h3>
                    </div>
                    <div class="card-body">
                        @if (session('messageType'))
                            <div class="alert alert-{{ session('messageType') }}">{{ session('messageContent') }}</div>
                        @endif

                        <form method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}">
                                @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" name="password">
                                @error('password')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col">
                                    <a class="btn btn-outline-primary" href="{{ route('user.register') }}">Register</a>
                                </div>
                                <div class="col text-right">
                                    <button type="submit" class="btn btn-primary">Login</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection