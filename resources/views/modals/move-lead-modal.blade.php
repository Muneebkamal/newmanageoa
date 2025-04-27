<div class="modal fade" id="moveCopyModal" tabindex="-1" aria-labelledby="moveCopyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moveCopyModalLabel">Move Item(s) to Buylist</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="moveCopyForm">
                    <div class="mb-3">
                        <label for="selectBuylist" class="form-label">Select Buylist</label>
                        <select class="form-select" id="selectBuylist" required>
                            <option value="" disabled selected>Select a Buylist</option>
                            <!-- Dynamically populate options here -->
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="copyLeadCheckbox">
                        <label class="form-check-label" for="copyLeadCheckbox">
                            Copy Lead(s)
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitMoveCopy">save</button>
            </div>
        </div>
    </div>
</div>
