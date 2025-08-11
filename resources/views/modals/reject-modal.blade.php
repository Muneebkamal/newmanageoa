<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form data-v-c14627b0="" class="form-inline" data-gtm-form-interact-id="2">
                    <div data-v-c14627b0="" class="form-group">
                        <label data-v-c14627b0="" for="rejectionReason" class="col-form-label mr-2">Rejection Reason:</label>
                        <select data-v-c14627b0="" name="reasonInput"  class="form-select select2" id="rejectionReason" class="custom-select custom-select-sm" data-gtm-form-interact-field-id="8">
                            {{-- <option data-v-c14627b0="" value="Out of stock">Out of stock</option>
                            <option data-v-c14627b0="" value="Source page error">Source page error</option>
                            <option data-v-c14627b0="" value="Coupon/Sale expired">Coupon/Sale expired</option>
                            <option data-v-c14627b0="" value="Failed Payment">Failed Payment</option>
                            <option data-v-c14627b0="" value="custom">Custom...</option> --}}
                        </select>
                    </div>
                    <div id="customReasonContainer" style="display: none;" data-v-c14627b0="">
                        <textarea id="customReason" class="form-control" placeholder="Please specify your reason..." rows="3"></textarea>
                    </div>
                </form>
                
                <script>
                    document.getElementById('rejectionReason').addEventListener('change', function() {
                        var selectedValue = this.value;
                        var customReasonContainer = document.getElementById('customReasonContainer');
                        
                        // Show or hide the text area based on the selected value
                        if (selectedValue === 'custom') {
                            customReasonContainer.style.display = 'block'; // Show text area
                        } else {
                            customReasonContainer.style.display = 'none'; // Hide text area
                        }
                    });
                </script>
                
                <input type="hidden" name="itemID" id="itemID">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="rejectSubmit">Reject Item</button>
            </div>
        </div>
    </div>
</div>
