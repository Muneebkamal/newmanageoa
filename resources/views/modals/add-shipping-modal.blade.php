<div class="modal fade" id="shippingBatchModal" tabindex="-1" role="dialog" aria-labelledby="shippingBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shippingBatchModalLabel">Create New Shipping Batch</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="newShippingBatchForm">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="newBatchName">Name</label>
                            <input type="text" id="shipping_name" class="form-control" placeholder="New Shipping Batch" autocomplete="off">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="newBatchShipDate">Ship Date</label>
                            <input type="date" id="shipping_date" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="newBatch">Marketplace</label>
                            <input type="text" id="market_place" class="form-control" placeholder="FBA">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="newBatchTrackingNumber">Tracking Number</label>
                            <input type="text" id="tracking_number" class="form-control" placeholder="ABC123" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="newBatchShippingNote">Shipping Batch Notes</label>
                        <textarea id="shipping_notes" rows="3" class="form-control" placeholder="Notes"></textarea>
                    </div>
                    <input type="hidden" name="p_idd" id="p_idd">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveBatchButton"  onclick="saveShipping()">
                    <i class="fa fa-floppy-o" aria-hidden="true"></i> Save
                </button>
                <button type="button" class="btn btn-outline-danger" data-dismiss="modal">
                    <i class="fa fa-times" aria-hidden="true"></i> Cancel
                </button>
            </div>
        </div>
    </div>
</div>
