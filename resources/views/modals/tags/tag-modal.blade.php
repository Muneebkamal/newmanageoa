@section('styles')
    <style>
        .tag-input {
            border: none;
            border-bottom: 1px solid black;
            outline: none;
        }

        .tag-input:focus {
            outline: none;
            border-bottom: 1px solid black;
        }
    </style>
@endsection

<!--  Small modal example -->
<div id="manage-tag-modal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog"
    aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mySmallModalLabel">Manage Tags</h5>
                {{-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button> --}}
            </div>
            <div class="modal-body">
                <form action="" id="tag-form">
                    @csrf
                    <div class="form-icon">
                        <input type="text" class="form-control form-control-icon" id="tag_name" name="name"
                            placeholder="Create a new tag">
                        <i class="bx bx-plus"></i>
                    </div>
                </form>
                <div id="tags-list" class="mt-2" style="overflow-y: auto; max-height: 30vh;">
                    {{-- <div class="d-flex justify-content-between align-items-center">
                        <div class="mb-2 d-flex align-items-center">
                            <i class="ri-price-tag-3-fill fs-3 text-primary tag-edit-btn"></i>

                            <i id="colorToggle" class="ri-checkbox-blank-fill dropdown-toggle tag-action-btns d-none"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                            <div class="dropdown-menu" style="--vz-dropdown-min-width: 1rem;">
                                <a class="dropdown-item" data-color="primary">
                                    <i class="ri-checkbox-blank-fill text-primary"></i>
                                </a>
                                <a class="dropdown-item" data-color="info">
                                    <i class="ri-checkbox-blank-fill text-info"></i>
                                </a>
                                <a class="dropdown-item" data-color="success">
                                    <i class="ri-checkbox-blank-fill text-success"></i>
                                </a>
                                <a class="dropdown-item" data-color="warning">
                                    <i class="ri-checkbox-blank-fill text-warning"></i>
                                </a>
                                <a class="dropdown-item" data-color="danger">
                                    <i class="ri-checkbox-blank-fill text-danger"></i>
                                </a>
                                <a class="dropdown-item" data-color="secondary">
                                    <i class="ri-checkbox-blank-fill text-secondary"></i>
                                </a>
                                <a class="dropdown-item" data-color="dark">
                                    <i class="ri-checkbox-blank-fill text-dark"></i>
                                </a>
                            </div>
                            <span class="ms-2">Not Match</span>
                        </div>
                        <div class="tag-edit-btn" onclick="tagEdit()">
                            <i class="ri-pencil-line me-2 fs-5"></i>
                        </div>
                        <div class="tag-action-btns d-none" onclick="tagEditDone()">
                            <i class="ri-check-fill me-2 fs-5"></i>
                            <br>
                            <i class="ri-delete-bin-6-line me-2 fs-5"></i>
                        </div>
                    </div> --}}
                </div>
            </div>
            <div class="modal-footer">
                {{-- <a href="javascript:void(0);" class="btn btn-link link-success fw-medium" data-bs-dismiss="modal"><i
                        class="ri-close-line me-1 align-middle"></i> Close</a> --}}
                <button type="button" class="btn btn-primary" id="submit-tags">Done</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
