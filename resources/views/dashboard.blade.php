@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="text-center">Welcome to Dashboard</h1>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Total Users
                    </div>
                    <div class="card-body">
                        <h3>{{ $totalUsers }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Total Orders
                    </div>
                    <div class="card-body">
                        <h3>{{ $totalOrders }}</h3>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
