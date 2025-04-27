<div class="modal fade" id="editShippingItemModal" tabindex="-1" aria-labelledby="editShippingItemModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title" id="editShippingItemModalLabel">Edit Shipping Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <!-- Modal Body -->
      <div class="modal-body">
        <div class="row">
          <div class="col">
            <h6>Shipping Event</h6>
            <form id="updateShipEvent" >
              <div class="row mb-3">
                <input type="hidden" name="ship_event_id" id="ship_event_id" value="">
                <div class="col-md-6">
                  <label for="shippingBatch" class="form-label">Shipping Batch</label>
                  <input type="text" id="shippingBatch" placeholder="Shipping batch" readonly class="form-control">
                </div>
                <div class="col-md-6">
                  <label for="itemsToShip" class="form-label"># To Ship</label>
                  <input type="number" id="itemsToShip" min="0" step="1" class="form-control">
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="qcCheck" class="form-label">QC Check</label>
                  <div class="dropdown">
                    <button type="button" class="btn btn-outline-primary dropdown-toggle" id="qcCheck" data-bs-toggle="dropdown" aria-expanded="false">
                      AZ MATCH?
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="qcCheck">
                      <li>
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" id="upcMatchesFlag">
                          <label class="form-check-label" for="upcMatchesFlag">UPC MATCHES</label>
                        </div>
                      </li>
                      <li>
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" id="titleMatchesFlag">
                          <label class="form-check-label" for="titleMatchesFlag">TITLE MATCHES</label>
                        </div>
                      </li>
                      <li>
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" id="imageMatchesFlag">
                          <label class="form-check-label" for="imageMatchesFlag">IMAGE MATCHES</label>
                        </div>
                      </li>
                      <li>
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" id="descriptionMatchesFlag">
                          <label class="form-check-label" for="descriptionMatchesFlag">DESCRIPTION MATCHES</label>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-md-6">
                  <label for="expirationDate" class="form-label">Expiration Date</label>
                  <input type="date" id="expirationDate" class="form-control">
                </div>
              </div>
              <!-- Additional Form Fields -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="asinOverride" class="form-label">ASIN Override</label>
                  <input type="text" id="asinOverride" placeholder="ABC123" class="form-control">
                </div>
                <div class="col-md-6">
                  <label for="titleOverride" class="form-label">Title Override</label>
                  <input type="text" id="titleOverride" placeholder="Title" class="form-control">
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="condition" class="form-label">Condition</label>
                  <input type="text" id="condition" placeholder="New" class="form-control">
                </div>
                <div class="col-md-6">
                  <label for="mskuOverride" class="form-label">MSKU Override</label>
                  <input type="text" id="mskuOverride" placeholder="MSKU" maxlength="40" class="form-control">
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="listPrice" class="form-label">Listing Price Override</label>
                  <input type="number" id="listPrice" min="0.00" step="0.1" class="form-control">
                </div>
                <div class="col-md-6">
                  <label for="minOverride" class="form-label">Min Override</label>
                  <input type="number" id="minOverride" min="0.00"step="0.1" class="form-control">
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="maxOverride" class="form-label">Max Override</label>
                  <input type="number" id="maxOverride" min="0.00" step="0.1" class="form-control">
                </div>
                <div class="col-md-6">
                  <label for="upcOverride" class="form-label">UPC Override</label>
                  <input type="text" id="upcOverride" class="form-control">
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="shippingNotes" class="form-label">Shipping Notes</label>
                  <textarea id="shippingNotes" rows="2" placeholder="Notes" class="form-control"></textarea>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="updateShipEvent()">Save</button>
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>
