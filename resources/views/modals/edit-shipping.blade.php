<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="editForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Shipping Batch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                    <!-- Form fields for each field you want to edit -->
                    <input type="hidden" id="edit_id" name="id">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_name">Name</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_date">Ship Date</label>
                                <input type="date" class="form-control" id="edit_date" name="date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_market_place">Marketplace</label>
                                <input type="text" class="form-control" id="edit_market_place" name="market_place" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_tracking_number">Tracking Number</label>
                                <input type="text" class="form-control" id="edit_tracking_number" name="tracking_number" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_notes">Shipping Batch Notes:</label>
                               <textarea class="form-control" id="edit_notes" name="notes" rows="2" cols="5"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_status">Status</label>
                                <select class="form-select" name="status" id="edit_status">
                                    <option value="open">Open</option>
                                    <option value="pending">Pending </option>
                                    <option value="in_transit">In Transit</option>
                                    <option value="closed">Closed </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    
                    
                   
                    
                    
                   
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
