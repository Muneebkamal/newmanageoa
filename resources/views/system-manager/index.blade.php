@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">System Manager</a></li>
                    <li class="breadcrumb-item active"><a href="{{ url('settings') }}">Settings</a></li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h2>Settings</h2>
                <!-- Nav Tabs -->
                <ul class="nav nav-pills animation-nav gap-2 mb-3" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab" aria-controls="system" aria-selected="false">
                            System Settings
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="cashback-tab" data-bs-toggle="tab" data-bs-target="#cashback" type="button" role="tab" aria-controls="cashback" aria-selected="true">
                            Cashback Sources
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab" aria-controls="rejected" aria-selected="false">
                            Rejected Reasons
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tags-tab" data-bs-toggle="tab" data-bs-target="#tags" type="button" role="tab" aria-controls="tags" aria-selected="false">
                            Tags
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="emails-tab" data-bs-toggle="tab" data-bs-target="#emails" type="button" role="tab" aria-controls="emails" aria-selected="false">
                            Emails
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="locations-tab" data-bs-toggle="tab" data-bs-target="#locations" type="button" role="tab" aria-controls="locations" aria-selected="false">
                            Locations
                        </button>
                    </li>
                </ul>   

                <!-- Tab Content -->
                <div class="tab-content" id="settingsTabsContent">
                    <!-- Ssytem sEttings Tab -->
                    <div class="tab-pane fade" id="system" role="tabpanel" aria-labelledby="system-tab">
                        <div class="card">
                            <div class="card-body">
                                <h4>System Settings</h4>
                                <form id="systemSettingsForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="per_page" class="form-label">Show Per Page</label>
                                        <select class="form-select" id="per_page" name="per_page">
                                            @php
                                                $options = [10, 25, 50, 100, 250, 500, -1];
                                                $selectedValue = $systemSettings['per_page'] ?? 10; // <-- pick saved value or fallback 10
                                            @endphp
                                            @foreach($options as $option)
                                                <option value="{{ $option }}" {{ (int)$selectedValue === $option ? 'selected' : '' }}>
                                                    {{ $option == -1 ? 'All' : $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Settings</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Cashback Source Tab -->
                    <div class="tab-pane fade show active" id="cashback" role="tabpanel" aria-labelledby="cashback-tab">
                        <div class="card">
                            <div class="card-head">
                                <button class="btn btn-primary float-end mt-2 mb-2" data-bs-toggle="modal" data-bs-target="#addCashbackSourceModal">
                                    Add New Cashback Source
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered" id="cashback-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rejected Reasons Tab -->
                    <div class="tab-pane fade" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
                        <div class="card">
                            <div class="card-head">
                                <button class="btn btn-primary float-end mt-2 mb-2" data-bs-toggle="modal" data-bs-target="#addRejectedReasonModal">
                                    Add New Rejected Reason
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered" id="rejected-table" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Reason</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tags Tab -->
                    <div class="tab-pane fade" id="tags" role="tabpanel" aria-labelledby="tags-tab">
                        <div class="card">
                            <div class="card-head">
                                <button class="btn btn-primary float-end mt-2 mb-2" data-bs-toggle="modal" data-bs-target="#addTagModal">
                                    Add New Tag
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered" id="tags-table" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Tag Name</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Emails Tab -->
                    <div class="tab-pane fade" id="emails" role="tabpanel" aria-labelledby="emails-tab">
                        <div class="card">
                            <div class="card-head">
                                <button class="btn btn-primary float-end mt-2 mb-2" data-bs-toggle="modal" data-bs-target="#emailModal" id="addNewEmail">
                                    Add New Email
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="emailTable" class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Email</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Locations Tab -->
                     <div class="tab-pane fade" id="locations" role="tabpanel" aria-labelledby="locations-tab">
                        <div class="card">
                            <div class="card-head">
                                <button class="btn btn-primary float-end mt-2 mb-2" id="addNewLocation">
                                    Add New Location
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="locationTable" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Location</th>
                                                <th>Type</th>
                                                <th>Street Address</th>
                                                <th>Street Address Line 2</th>
                                                <th>City</th>
                                                <th>State</th>
                                                <th>Zip Code</th>
                                                <th>Country</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div> <!-- End Tab Content -->
            </div>
        </div>
    </div>
</div>
<!--Locations Modal -->
<div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="locationForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="locationModalLabel">Add Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="locationId" name="id">

                    <!-- Location -->
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" id="location" name="location" class="form-control" required>
                    </div>

                    <!-- Type -->
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select id="type" name="type" class="form-control" required>
                            <option value="prep">Prep</option>
                            <option value="inhouse">In House</option>
                        </select>
                    </div>

                    <!-- Address Fields (Two Columns) -->
                    <div class="row">
                        <!-- Street Address -->
                        <div class="col-12 col-md-6 mb-3">
                            <label for="street_address" class="form-label">Street Address</label>
                            <input type="text" id="street_address" name="street_address" class="form-control" required>
                        </div>

                        <!-- Apartment -->
                        <div class="col-12 col-md-6 mb-3">
                            <label for="apartment" class="form-label">Street Address Line 2</label>
                            <input type="text" id="apartment" name="apartment" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <!-- City -->
                        <div class="col-12 col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" id="city" name="city" class="form-control" required>
                        </div>

                        <!-- State -->
                        <div class="col-12 col-md-6 mb-3">
                            <label for="state" class="form-label">State</label>
                            <input type="text" id="state" name="state" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Country -->
                        <div class="col-12 col-md-6 mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" id="country" name="country" value="USA" class="form-control" required>
                        </div>

                        <!-- Zip Code -->
                        <div class="col-12 col-md-6 mb-3">
                            <label for="zip" class="form-label">Zip Code</label>
                            <input type="text" id="zip" name="zip" class="form-control" required>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="emailForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">Add New Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="emailId" name="id">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="Enter email address">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveEmail">Save Email</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal for Adding Cashback Source -->
<div class="modal fade" id="addCashbackSourceModal" tabindex="-1" aria-labelledby="addCashbackSourceLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCashbackSourceLabel">Add Cashback Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addCashbackSourceForm">
                    <input type="hidden" name="cash_back_id" id="cash_back_id">
                    <div class="mb-3">
                        <label class="form-label">Cashback Source Name</label>
                        <input type="text" class="form-control" id="cashback-source-name" name="name" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Cashback Source</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding Rejected Reason -->
<div class="modal fade" id="addRejectedReasonModal" tabindex="-1" aria-labelledby="addRejectedReasonLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRejectedReasonLabel">Add Rejected Reason</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addRejectedReasonForm">
                    <input type="hidden" name="reason_id" id="reason_id">
                    <div class="mb-3">
                        <label class="form-label">Rejected Reason</label>
                        <input type="text" class="form-control" id="rejected-reason-name" name="reason" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Reason</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding Tag -->
<div class="modal fade" id="addTagModal" tabindex="-1" aria-labelledby="addTagLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTagLabel">Add Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addTagForm">
                    <input type="hidden" name="tag_id" id="tag_id">
                    <div class="mb-3">
                        <label class="form-label">Tag Name</label>
                        <input type="text" class="form-control" id="tag-name" name="name" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Tag</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
$(function () {
    // Cashback Table
    var cashbackTable = $('#cashback-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("cashbacks.data") }}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });

    // Rejected Table
    var rejectedTable = $('#rejected-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("rejected-reasons.data") }}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'reason', name: 'reason' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });

    // Tags Table
    var tagsTable = $('#tags-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("tags.data.list") }}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });

    // Cashback Submit
    $('#addCashbackSourceForm').on('submit', function (e) {
        e.preventDefault();
        $.post('{{ url("add-cashback-source") }}', $(this).serialize(), function () {
            $('#addCashbackSourceModal').modal('hide');
            $('#addCashbackSourceForm')[0].reset();
            cashbackTable.draw();
        }).fail(() => alert('Failed to add cashback source.'));
    });

    // Rejected Reason Submit
    $('#addRejectedReasonForm').on('submit', function (e) {
        e.preventDefault();

        $.post('{{ url("add-rejected-reason") }}', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            reason_id: $('#reason_id').val(),
            reason: $('#rejected-reason-name').val()
        }, function () {
            $('#addRejectedReasonModal').modal('hide');
            $('#addRejectedReasonForm')[0].reset();
            $('#reason_id').val('');
            $('#rejected-reason-name').val('');
            rejectedTable.draw();
        }).fail(() => alert('Failed to add rejected reason.'));
    });
    $('#addTagForm').on('submit', function (e) {
        e.preventDefault();
        $.post('{{ url("add-tag-data") }}', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            tag_id: $('#tag_id').val(),
            name: $('#tag-name').val()
        }, function () {
            $('#addTagModal').modal('hide');
            $('#addTagForm')[0].reset();
            $('#tag_id').val('');
            $('#tag-name').val('');
            tagsTable.draw();
        }).fail(() => alert('Failed to add rejected reason.'));
    });
});


    // Edit Functions
    function editCashback(id, name) {
        $('#cash_back_id').val(id);
        $('#cashback-source-name').val(name);
        $('#addCashbackSourceModal').modal('show');
    }
    function editRejectedReason(id, reason) {
        $('#reason_id').val(id);
        $('#rejected-reason-name').val(reason);
        $('#addRejectedReasonModal').modal('show');
    }
    function editTag(id, name) {
        $('#tag_id').val(id);
        $('#tag-name').val(name);
        $('#addTagModal').modal('show');
    }
    const emailTable = $('#emailTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('emails.index') }}',
        columns: [
            { data: 'email', name: 'email' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            { width: '70%', targets: 0 }, // Adjust the email column width
            { width: '30%', targets: 1 }  // Adjust the action column width
        ],
        autoWidth: false // Disable auto width to allow manual settings
    });
    // Reset modal for adding new email
    $('#addNewEmail').on('click', function() {
        $('#emailModalLabel').text('Add New Email');
        $('#emailForm')[0].reset();
        $('#emailId').val('');
    });
    // Handle Add/Edit Email Form Submission
    $('#emailForm').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serialize();
        const url = $('#emailId').val() ? '{{ route('emails.update', ':id') }}'.replace(':id', $('#emailId').val()) : '{{ route('emails.store') }}';

        $.ajax({
            url: url,
            method: $('#emailId').val() ? 'PUT' : 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#emailModal').modal('hide');
                    emailTable.ajax.reload();
                    toastr.success(response.message || 'Email saved successfully!');
                } else {
                    toastr.error(response.message || 'An error occurred.');
                }
            },
            error: function() {
                toastr.error('An error occurred. Please try again.');
            }
        });
    });
    // Edit Email
    $('#emailTable').on('click', '.edit-email', function() {
        const id = $(this).data('id');
        const email = $(this).data('email');

        $('#emailModalLabel').text('Edit Email');
        $('#emailId').val(id);
        $('#email').val(email);
        $('#emailModal').modal('show');
    });
    // Delete Email
    $('#emailTable').on('click', '.delete-email', function() {
        const id = $(this).data('id');
        
        Swal.fire({
        title: 'Are you sure?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route('emails.destroy', ':id') }}'.replace(':id', id),
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            emailTable.ajax.reload();
                            Swal.fire(
                                'Deleted!',
                                response.message || 'The email has been deleted.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message || 'An error occurred while deleting the email.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'An error occurred. Please try again.',
                            'error'
                        );
                    }
                });
            }
        });
    });
    //locatosn js 
    const locationTable = $('#locationTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('locations.list') }}',
        columns: [
            { data: 'location', name: 'location' },
            { data: 'type', name: 'type' },
            { data: 'street_address', name: 'street_address' },
            { data: 'apartment', name: 'apartment' },
            { data: 'city', name: 'city' },
            { data: 'state', name: 'state' },
            { data: 'country', name: 'country' },
            { data: 'zip', name: 'zip' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            { width: '10%', targets: 0 }, // Adjust the email column width
            { width: '10%', targets: 1 },  // Adjust the action column width
            { width: '20%', targets: 1 }  // Adjust the action column width
        ],
        autoWidth: false // Disable auto width to allow manual settings
    });

    // Add Location
    $('#addNewLocation').click(function() {
        $('#locationModalLabel').text('Add Location');
        $('#locationForm')[0].reset();
        $('#locationId').val('');
        $('#locationModal').modal('show');
    });

    // Submit Location Form
    $('#locationForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        const url = $('#locationId').val() ? '{{ route('locations.update', ':id') }}'.replace(':id', $('#locationId').val()) : '{{ route('locations.store') }}';

        $.ajax({
            url: url,
            method: $('#locationId').val() ? 'PUT' : 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            success: function(response) {
                $('#locationModal').modal('hide');
                locationTable.ajax.reload();
                toastr.success(response.message);
            },
            error: function() {
                toastr.error('An error occurred.');
            }
        });
    });

    // Edit Location
    $('#locationTable').on('click', '.edit-location', function() {
        const id = $(this).data('id');
        const location = $(this).data('location');
        const type = $(this).data('type');
        const streetAddress = $(this).data('street_address');
        const apartment = $(this).data('apartment');
        const city = $(this).data('city');
        const state = $(this).data('state');
        const country = $(this).data('country');
        const zip = $(this).data('zip');

        // Set the modal title to 'Edit Location'
        $('#locationModalLabel').text('Edit Location');

        // Populate the modal fields with the data
        $('#locationId').val(id);
        $('#location').val(location);
        $('#type').val(type);
        $('#street_address').val(streetAddress);
        $('#apartment').val(apartment);
        $('#city').val(city);
        $('#state').val(state);
        $('#country').val(country);
        $('#zip').val(zip);

        // Show the modal
        $('#locationModal').modal('show');

    });

    // Delete Location
    $('#locationTable').on('click', '.delete-location', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route('locations.destroy', ':id') }}'.replace(':id', id),
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    success: function(response) {
                        locationTable.ajax.reload();
                        Swal.fire('Deleted!', response.message, 'success');
                    },
                    error: function() {
                        Swal.fire('Error!', 'An error occurred.', 'error');
                    }
                });
            }
        });
    });
    $('#systemSettingsForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route("system-settings.save") }}',
            type: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Settings saved successfully!');
                } else {
                    toastr.error(response.message || 'Failed to save settings.');
                }
            },
            error: function(xhr) {
                toastr.error('Something went wrong while saving.');
            }
        });
    });

</script>
@endsection
