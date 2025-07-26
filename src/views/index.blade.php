@extends('layouts.layoutMaster')

@section('title', 'Services')

@section('page-style')
    <link rel="stylesheet" href="{{asset("assets/vendor/app-service/css/style.css")}}">
@endsection

@section('page-script')

@endsection

@section('content')
    <h2>Services</h2>

    <div class="card">
        <div class="card-datatable table-responsive">
            <table class="datatables-permissions table border-top">
                <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <th>#</th>
                    <th>Name</th>
                    <th>API Access</th>
                    <th>STATUS</th>
                    <th>ACTION</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
    <!--/ Layout services -->
@endsection
