<!-- Small Modal with Search Field -->
<div id="select-tag-modal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog"
    aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mySmallModalLabel"><span id="tag-asin"></span> Tags</h5>
            </div>
            <div class="modal-body">
                <!-- Input Group with Search Icon and Plus Icon on the Right -->
                <div class="input-group">

                    <input id="tag-search" type="text" class="form-control" placeholder="Enter tag name"
                        aria-describedby="basic-addon1">
                    <!-- Search Icon (on the left) -->
                    <span class="input-group-text" id="basic-addon1">
                        <i class="bx bx-search"></i>
                    </span>

                    <!-- Input Field -->
                </div>
                <input type="hidden" id="lead-id-tag">
                <input type="hidden" id="selected_tags">

                <div class="mt-2" style="overflow-y: auto; max-height: 30vh;" id="tags-list-lead">
                    <!-- Dynamic tag checkboxes will be appended here -->
                </div>
            </div>
            <div class="modal-footer">
                <button id="submit-lead-tags" type="button" class="btn btn-primary">Done</button>
            </div>
        </div>
    </div>
</div>
