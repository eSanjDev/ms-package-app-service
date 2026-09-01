@extends('layouts.master')

@section("title",'Create New Service')

@section('page-style')
    @vite([
    'resources/assets/packages/app-service/css/services-add-new.css'
])
@endsection

@section('page-script')
    @vite([
'resources/assets/packages/app-service/js/service.js'
])
@endsection

@section('content')
    <h2>Add New Service</h2>
    <form class="form-setting mt-2" action="{{route("services.store")}}" method="post">
        @csrf
        <div class="card p-6">

            <div class="row">
                <h3 class="mb-5">Service Info</h3>
                <div class="col-lg-4 mb-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" value="{{old('name')}}"
                               placeholder="Enter Service Name" name="name"/>
                        <label class="form-label">Name</label>
                    </div>
                    @error('name')
                    <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="form-floating position-relative">
                        <input type="text" class="form-control" value="{{old('client_id')}}"
                               placeholder="Enter Client Id" name="client_id" id="clientID"/>
                        <label class="form-label">Client ID</label>
                        <button class="btn btn-validate" data-url="{{route('services.validation')}}" type="button" id="validation-client">Validate</button>
                    </div>
                    @error('client_id')
                    <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="col-lg-4 px-2 position-relative select-box mb-4">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-select select2">
                        <option @selected(old('is_active', 1) == 0) value="0">Deactive</option>
                        <option @selected(old('is_active',1) == 1) value="1">Active</option>
                    </select>
                </div>
            </div>

            <div class="row mt-5">
                <h3 class="mb-5">Permissions</h3>
                <div class="col-12">
                    @error('permissions')
                    <div class="text-danger">{{ $message }}</div> @enderror
                    <div class="table-responsive">
                        <table class="table table-flush-spacing">
                            <tbody>
                            @foreach($permissions as $index => $groups)
                                @foreach($groups as $key => $name)
                                    <tr>
                                        <td class="text-nowrap fw-medium text-heading">{{$name}}</td>
                                        <td>
                                            <div class="d-flex justify-content-evenly">
                                                <div class="form-check mb-0 ">
                                                    <input class="form-check-input" name="permissions[]"
                                                           @checked(in_array($key,old('permissions',[]))) type="checkbox"
                                                           value="{{$key}}" id="permission-{{$key}}">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @foreach(config('esanj.app_service.extra_fields') as $field)
                @include($field)
            @endforeach

            <div class="row mt-5">
                <div class="col-12">
                    <button class="btn btn-primary mt-3">Create Service</button>
                </div>
            </div>
        </div>
    </form>
@endsection
