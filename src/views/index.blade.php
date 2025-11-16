@extends('layouts.master')

@section('title', 'Services')


<!-- Vendor Styles -->
@section('vendor-style')
    @vite([
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
])
@endsection

@section('vendor-script')
    @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
])
@endsection

@section('page-style')
    @vite([
    'resources/assets/packages/app-service/css/services.css'
])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite([
    'resources/assets/packages/app-service/js/service-table.js',
])
@endsection

@section('content')
    <h2>Services</h2>

    <div class="card">
        <div class="card-datatable table-responsive">
            <table class="datatables-ajax table border-top"></table>
        </div>
    </div>
@endsection
