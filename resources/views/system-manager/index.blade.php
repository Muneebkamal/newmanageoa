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

                </div> <!-- End Tab Content -->
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
</script>
@endsection
