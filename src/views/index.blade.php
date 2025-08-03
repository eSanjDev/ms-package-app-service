@extends('layouts.layoutMaster')

@section('title', 'Services')

@section('page-style')
    <link rel="stylesheet" href="{{asset("assets/vendor/app-service/css/services-table.css")}}">
@endsection

@section('page-script')
    <script>
        @if(request()->filled('only_trash'))
            window.only_trash = 1;
        @endif
    </script>
    <script>
        window.baseUrlApiAdmin = "{{config('app-service.routes.api_prefix')}}"
        window.baseUrlAdmin = "{{config('app-service.routes.web_prefix')}}"
    </script>
    <script type="module" src="{{asset('assets/vendor/app-service/js/ServiceTable.js')}}"></script>
@endsection

@section('content')

    <!-- Layout services -->
    <div class="layout-services">
        <h2>Services</h2>
        <!-- DataTable with Buttons -->
        <div class="card">
            <div class="card-datatable table-responsive">
                <table class="datatables-ajax table border-top"></table>
            </div>
        </div>
        <!--/ Layout services -->
    </div>
@endsection
