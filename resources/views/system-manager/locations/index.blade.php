@extends('layouts.app')

@section('title', 'Locations')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">System Manager</a></li>
                    <li class="breadcrumb-item active"><a href="{{ url('locations') }}">Locations</a></li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 ">
        <div class="card ">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Locations</h4>
                <button id="addNewLocation" class="btn btn-primary">Add Location</button>
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
</div>

<!-- Modal -->
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


@endsection

@section('script')
<script>
    $(document).ready(function() {
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
    });
</script>
@endsection
