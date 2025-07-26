@extends('layouts.layoutMaster')

@section('page-style')
    <link rel="stylesheet" href="{{asset('/assets/vendor/app-service/css/style.css')}}">
@endsection
@section('content')
    <div class="layout-services">
        <h2>Edit Service</h2>
        <form class="form-setting mt-2" action="{{route("admin.services.update",$service->id)}}" method="post">
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
                                    <button class="btn btn-validate" type="button">Validate</button>
                                </div>
                                @error('client_id')
                                <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-lg-4 px-2 position-relative select-box mb-4">
                                <label class="form-label" for="validationStatus">Status</label>
                                <select name="is_active" class="form-select select2">
                                    <option @selected(old('is_active',$service->is_active) == 0) value="0">deactive
                                    </option>
                                    <option @selected(old('is_active',$service->is_active) == 1) value="1">Active</option>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <button class="btn btn-primary">Update Service</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
