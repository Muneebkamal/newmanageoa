@extends('layouts.app')

@section('title', 'Add Employee')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add Employee</h3>
                </div>
                <div class="card-body">

                    <form id="myForm" action="{{ route('employees.store') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-6 mb-0">
                                <div class="form-group">
                                    <label for="name1">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name"
                                        placeholder="Enter First Name" value="" required="">
                                </div>
                            </div>
                            <div class="form-group col-md-6 mb-0">
                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name"
                                        placeholder="Enter Last Name" value="" required="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 mb-0">
                                <div class="form-group">
                                    <label for="email">Username</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="Enter Username" value="" required="">
                                </div>
                            </div>
                            <div class="form-group col-md-6 mb-0">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="Enter Email" value="" required="">
                                    <div data-lastpass-icon-root=""
                                        style="position: relative !important; height: 0px !important; width: 0px !important; float: left !important;">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 mb-0">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control" name="password" id="password"
                                        placeholder="Enter Password" required="">
                                    <div data-lastpass-icon-root=""
                                        style="position: relative !important; height: 0px !important; width: 0px !important; float: left !important;">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-6 mb-0">
                                <div class="form-group">
                                    <label for="department_id">Departments</label>
                                    <select class="form-control select2 " name="department_id"
                                        id="department_id" required="" tabindex="-1" aria-hidden="true">
                                        <option value="" selected="" disabled="">Select Department</option>
                                        <option value="Product Reacher">Product Reacher</option>
                                        <option value="Product Reach Admin">Product Reach Admin</option>
                                        <option value="Buyer">Buyer</option>
                                        <option value="Prep Center">Prep Center</option>
                                        <option value="Manager">Manager</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 mb-0">
                                <label for="status" class="mt-1">Status</label>
                                <div class="form-group">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="status" id="active" value="1">
                                        <label class="form-check-label" for="active" >
                                            Active
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="status" id="inactive" value="0">
                                        <label class="form-check-label" for="inactive">
                                            Inactive
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department_id">Roles</label>
                                    <select class="form-control select2 " name="role_id"
                                        id="role_id" required="" tabindex="-1" aria-hidden="true">
                                        <option value="" selected="" disabled="">Select Rrole</option>
                                        @foreach ($roles as $role)
                                            @if($role->id != 1)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department_id">Sync Leads Url</label>
                                    <input type="text" name="sync_lead_url" id="sync_lead_url" class="form-control" value="">
                                </div>
                            </div>
                        </div>
                        <div class="form-footer mt-2 float-end">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
