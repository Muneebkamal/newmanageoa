@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Employee</h3>
                </div>
                <div class="card-body">

                    <form id="myForm" action="{{ route('employees.update',$employee->id) }}" method="post">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="form-group col-md-6 mb-0">
                                <div class="form-group">
                                    <label for="name1">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name"
                                        placeholder="Enter First Name" value="{{ $employee->first_name }}" required="">
                                </div>
                            </div>
                            <div class="form-group col-md-6 mb-0">
                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name"
                                        placeholder="Enter Last Name" value="{{ $employee->last_name }}" required="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 mb-0">
                                <div class="form-group">
                                    <label for="email">Username</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="Enter Username" value="{{ $employee->name }}" required="">
                                        <input type="hidden" class="form-control" id="employee_id" name="employee_id"
                                        placeholder="Enter Username" value="{{ $employee->id }}" required="">
                                </div>
                            </div>
                            <div class="form-group col-md-6 mb-0">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="Enter Email" value="{{ $employee->email }}" readonly>
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
                                        placeholder="Enter Password"  value="">
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
                                        <option value="Product Reacher" {{ $employee->department_id =='Product Reacher'?'selected':'' }}>Product Reacher</option>
                                        <option value="Product Reach Admin" {{ $employee->department_id =='Product Reach Admin'?'selected':'' }}>Product Reach Admin</option>
                                        <option value="Buyer" {{ $employee->department_id =='Buyer'?'selected':'' }}>Buyer</option>
                                        <option {{ $employee->department_id =='Prep Center'?'selected':'' }} value="Prep Center">Prep Center</option>
                                        <option {{ $employee->department_id =='Manager'?'selected':'' }} value="Manager">Manager</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 mb-0">
                                <label for="status" class="mt-1">Status</label>
                                <div class="form-group">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" value="1" name="status" id="active" {{ $employee->status ==1?'checked':'' }}>
                                        <label class="form-check-label" for="active">
                                            Active
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" {{ $employee->status ==0?'checked':'' }} value="0" name="status" id="inactive">
                                        <label class="form-check-label" for="inactive">
                                            Inactive
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department_id">Roles</label>
                                    <select class="form-control select2 "disabled name="role_id"
                                        id="role_id" required="" tabindex="-1" aria-hidden="true">
                                        <option value="" selected="" disabled="">Select Rrole</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}" {{ $role->id == $employee->role_id?'selected':'' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department_id" class="d-flex  justify-content-between"> <span>Sync Leads Url</span>
                                        <span> <button id="fetchLeadsBtn" type="button" class="btn btn-sm btn-primary"><i class="ri-refresh-fill"></i> Refresh Leads</button> </span>

                                    </label>
                                    <input type="text" name="sync_lead_url" id="sync_lead_url" class="form-control" value="{{ $employee->sync_lead_url }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <span id="leadStatus"></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            <div class="form-check">
                                @foreach($permissions as $permission)
                                    <div class="form-check">
                                        <input class="form-check-input" id="{{ $permission->id }}" type="checkbox" name="permissions[]" value="{{ $permission->name }}" 
                                            {{ in_array($permission->name, $employeePermissions) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                @endforeach
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
@section('script')
<script>
    $(document).ready(function () {
    $("#fetchLeadsBtn").on("click", function () {
        let employeeId = $("#employee_id").val(); // Get employee ID from input
        let sync_lead_url = $("#sync_lead_url").val(); // Get employee ID from input

        if (!employeeId) {
            alert("Please enter an Employee ID");
            return;
        }
        if (!sync_lead_url) {
            alert("Please enter an Employee ID");
            return;
        }

        $("#leadStatus").html("Fetching leads...");

        $.ajax({
            url: "/get-employee-leads/" + employeeId, 
            type: "GET",
            dataType: "json",
            data:{
                sync_lead_url:sync_lead_url
            },
            success: function (response) {
                if (response.success) {
                    $("#leadStatus").html(
                        `Leads imported successfully! <br> Total  Processed: <b>${response.inserted_count}</b>`
                    );
                } else {
                    $("#leadStatus").html(
                        "Error: " + response.error
                    );
                }
            },
            error: function (xhr) {
                $("#leadStatus").html(
                    "Failed to fetch leads. Please try again."
                );
            },
        });
    });
});

</script>
@endsection
