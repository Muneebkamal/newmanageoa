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
                <ul class="nav nav-pills animation-nav  gap-2 mb-3" id="settingsTabs" role="tablist">
                    <li class="nav-item waves-effect waves-light" role="presentation">
                        <button class="nav-link" id="cashback-tab" data-bs-toggle="tab" data-bs-target="#cashback" type="button" role="tab" aria-controls="cashback" aria-selected="true"></button>
                    </li>
                    <!-- Add more tabs as needed -->
                </ul>   

                <!-- Tab Content -->
                <div class="tab-content" id="settingsTabsContent">
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
                                        <tbody id="cashback-sources-body">
                                            <!-- Dynamic rows will be added here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding Cashback Source -->
<div class="modal fade" id="addCashbackSourceModal" tabindex="-1" aria-labelledby="addCashbackSourceLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCashbackSourceLabel">Add Cashback Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCashbackSourceForm">
                    <input type="hidden" name="cash_back_id" id="cash_back_id">
                    <div class="mb-3">
                        <label for="cashback-source-name" class="form-label">Cashback Source Name</label>
                        <input type="text" class="form-control" id="cashback-source-name" name="name" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Cashback Source</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    $(document).ready(function () {
        $('#addCashbackSourceForm').on('submit', function (e) {
            e.preventDefault();
            let cashbackSourceName = $('#cashback-source-name').val();
            let cash_back_id = $('#cash_back_id').val();
            $.ajax({
                url: `{{url('add-cashback-source') }}`, // Update with your endpoint
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                data: { name: cashbackSourceName,cash_back_id:cash_back_id },
                success: function (response) {
                    // Close the modal
                    $('#addCashbackSourceModal').modal('hide');
                    // Reset the form
                    $('#addCashbackSourceForm')[0].reset();
                    // Refresh the DataTable
                    table.draw(); // Refresh the table data
                },
                error: function () {
                    alert('Failed to add cashback source.');
                }
            });
        });
        var table = $('#cashback-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("cashbacks.data") }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'actions', name: 'actions' },
            ]
        });
    });
    function editCashback(id,name){
        $('#cash_back_id').val(id);
        $('#cashback-source-name').val(name);
        $('#addCashbackSourceModal').modal('show');
    }

</script>
@endsection
