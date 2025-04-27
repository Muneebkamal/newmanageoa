@extends('layouts.app')

@section('title', 'Emails')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">System Manager</a></li>
                    <li class="breadcrumb-item active"><a href="{{ url('emails') }}">Emails</a></li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Emails</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#emailModal" id="addNewEmail">Add New</button>
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
@endsection

@section('script')
<script>
    $(document).ready(function() {
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
    });
</script>
@endsection
