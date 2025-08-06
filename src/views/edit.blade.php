@extends('layouts.layoutMaster')

@section('title', 'Edit Service')

@section('page-style')
    <link rel="stylesheet" href="{{asset('/assets/vendor/app-service/css/services-add-new.css')}}">
@endsection

@section('page-script')
    <script>
        window.baseUrlApiAdmin = "{{config('app_service.routes.api_prefix')}}"
        window.baseUrlAdmin = "{{config('app_service.routes.web_prefix')}}"
    </script>
    <script src="{{asset('assets/vendor/app-service/js/service.js')}}"></script>
@endsection

@section('content')
    <div class="layout-services">
        <h2>Edit Service</h2>
        <form class="form-setting mt-2" action="{{route("services.update",$service->id)}}" method="post">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{session('success')}}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @csrf
            @method('put')
            <div class="row ">
                <div class="col-12">
                    <div class="card p-6">
                        <h3 class="mb-6">Service Info</h3>
                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" value="{{old('name',$service->name)}}"
                                           placeholder="Enter Service Name" name="name"/>
                                    <label class="form-label">Name</label>
                                </div>
                                @error('name')
                                <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-lg-4 mb-3">
                                <div class="form-floating position-relative">
                                    <input type="text" class="form-control"
                                           value="{{old('client_id',$service->client_id)}}"
                                           placeholder="Enter Client Id" name="client_id" id="clientID"/>
                                    <label class="form-label">Client ID</label>
                                    <button class="btn btn-validate" id="validation-client" type="button">Validate
                                    </button>
                                </div>
                                @error('client_id')
                                <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-lg-4 px-2 position-relative select-box mb-4">
                                <label class="form-label" for="validationStatus">Status</label>
                                <select name="is_active" class="form-select select2">
                                    <option @selected(old('is_active',$service->is_active) == 0) value="0">deactive
                                    </option>
                                    <option @selected(old('is_active',$service->is_active) == 1) value="1">Active
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <button class="btn btn-primary">Update Service</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                @foreach(config('app_service.extra_fields') as $field)
                    <div class="col-12">
                        @include(\Illuminate\Support\Str::endsWith('.blade.php')?$field:"$field.blade.php")
                    </div>
                @endforeach
            </div>
        </form>
    </div>
@endsection
