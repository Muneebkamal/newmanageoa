 <!-- Modal Structure -->
 <div class="modal fade" id="editITemModal" tabindex="-1" role="dialog" aria-labelledby="addAttachmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <h4>Order Item Information</h4>

                    <div class="col-md-4">
                        <label for="editListPrice" class="form-label">Units to Purchase</label>
                        <div class="input-group">
                            <button type="button" data-type="minus" class="btn btn-outline-secondary btn-number">
                                <span class="fa fa-minus"></span>
                            </button>
                            <input type="text" name="quant[1]" id="editQty" min="1" class="form-control input-number">
                            <button type="button" data-type="plus" class="btn btn-outline-secondary btn-number">
                                <span class="fa fa-plus"></span>
                            </button>
                        </div>
                        <div class="">
                            <label for="editListPrice" class="form-label">List Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" id="editListPrice" class="form-control">
                            </div>
                        </div>
                        <div class="">
                            <label for="editMinPrice" class="form-label">Min</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" id="editMinPrice" class="form-control">
                            </div>
                        </div>
                        <div class="">
                            <label for="editMaxPrice" class="form-label">Max</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" id="editMaxPrice" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="mb-3 row">
                            <div class="col">
                                <label for="editUpc" class="form-label">UPC</label>
                                <input type="text" id="editUpc" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col">
                                <label for="editMsku" class="form-label">MSKU</label>
                                <input type="text" id="editMsku" maxlength="40" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col">
                                <label for="editOrderNote" class="form-label">Product/Buyer Notes</label>
                                <textarea rows="2" id="editOrderNote" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col">
                                <label for="editShippingNote" class="form-label">Shipping Note</label>
                                <textarea rows="2" id="editShippingNote" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Save</button>
                </div>
                <hr>
                <h5>Lead Information</h5>
                        <form class="form-group justify-content-center">
                            <div class="form-group row">
                                <div class="col">
                                    <label for="leadName" class="col-sm-2 col-form-label">Name</label>
                                    <div class="col-sm-auto">
                                        <textarea rows="2" name="orderName" id="orderName" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="asin" class="col col-form-label">ASIN <a class="text-info" id="amazonUrl" href="" target="_blank">Amazon Link</a></label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="orderAsin" id="orderAsin" class="form-control">
                                    </div>
                                    <label for="category" class="col-sm-2 col-form-label">Category</label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="orderCategory" id="orderCategory" class="form-control">
                                    </div>
                                    <label for="supplier" class="col-sm-2 col-form-label">Supplier</label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="orderSupplier" id="orderSupplier" class="form-control">
                                    </div>
                                    <label for="sourceUrl" class="col-sm-auto col-form-label">Source URL <a href="" target="_blank" class="source_url text-info">Link to source</a></label>
                                    <div class="col-sm-auto">
                                       <div class="input-group">
                                            <span class="input-group-text">#</span>
                                            <input type="url" name="orderSourceUrl" id="orderSourceUrl" class="form-control">
                                       </div>
                                    </div>
                                    <label for="productNote" class="col-sm-auto col-form-label">Lead Note</label>
                                    <div class="col-sm-auto">
                                        <textarea rows="2" name="orderProductNote" id="orderProductNote" class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="cost" class="col-sm-auto col-form-label">Cost</label>
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.1" name="orderCost" id="orderCost" class="form-control" value="0.00">
                                        </div>
                                    </div>
                                    <label for="sellingPrice" class="col-sm-auto col-form-label">Selling Price</label>
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.1" name="orderSellingPrice" id="orderSellingPrice" class="form-control" value="0.00">
                                       </div>
                                        
                                    </div>
                                    <label for="orderBsr" class="col-sm-auto col-form-label">90 Day Avg</label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="orderBsr" id="orderBsr" class="form-control">
                                    </div>
                                    <label for="promo" class="col-sm-auto col-form-label">Promo</label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="orderPromo" id="orderPromo" class="form-control">
                                    </div>
                                    <label for="coupon_code" class="col-sm-auto col-form-label">Coupon Code</label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="coupon_code" id="coupon_code" class="form-control">
                                    </div>
                                    <label for="itemFlags" class="col-sm-auto col-form-label">Lead Flags</label>
                                    <div class="col-sm-auto" id="itemFlags">
                                        <div role="group" aria-label="Item toggles" class="btn-group">
                                            <button type="button" class="btn btn-light">
                                                <div class="form-check me-2">
                                                    <input class="form-check-input" type="checkbox" id="orderIsHazmat" name="orderIsHazmat">
                                                    <label class="form-check-label" for="orderIsHazmat">Hazmat</label>
                                                </div>
                                            </button>
                                            <button type="button" class="btn btn-light">
                                                <div class="form-check me-2">
                                                    <input class="form-check-input" type="checkbox" id="orderIsDisputed" name="orderIsDisputed">
                                                    <label class="form-check-label" for="orderIsDisputed">Disputed</label>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Save</button>
            </div>
        </div>
        
    </div>
  </div>
  