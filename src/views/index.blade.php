@extends('layouts.layoutMaster')

@section('title', 'Services')

@section('page-style')
    <link rel="stylesheet" href="{{asset('assets/vendor/app-service/css/services.css')}}">
@endsection

@section('vendor-style')
    <link rel="stylesheet" href="{{asset('assets/vendor/app-service/libs/datatables-bs5/datatables.bootstrap5.css')}}">
@endsection

@section('vendor-script')
    <script type="module"
            src="{{asset('assets/vendor/app-service/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
@endsection

@section('page-script')
    <script>
        window.baseUrlApiAdmin = "{{config('app_service.routes.api_prefix')}}"
        window.baseUrlAdmin = "{{config('app_service.routes.web_prefix')}}"
        @if(request()->filled('only_trash'))
            window.only_trash = 1;
        @endif
    </script>
    <script type="module" src="{{asset('assets/vendor/app-service/js/service-table.js')}}"></script>
@endsection

@section('content')
    <h2>Services</h2>

    <div class="card">
        <div class="card-datatable table-responsive">
            <table class="datatables-ajax table border-top"></table>
        </div>
    </div>
@endsection
