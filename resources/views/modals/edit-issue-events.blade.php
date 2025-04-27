<!-- Unified Modal -->
<div class="modal fade" id="EditeventModal" tabindex="-1" aria-labelledby="EditeventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="EditeventModalLabel">Edit Shipping Issue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="editevnetID" id="editevnetID">
                    <input type="hidden" name="eveNEditType" id="eveNEditType">
                    <div class="col" id="dyamic_data"></div>
                </div>
            </div>
            <div data-v-9e05b5cc="" class="modal-footer">
                <button data-v-9e05b5cc="" type="button" class="btn btn-primary" onclick="updateIssueEnvet()">Save</button> 
                <button data-v-9e05b5cc="" type="button" class="btn btn-danger">Cancel</button>
            </div>
        </div>
    </div>
</div>
