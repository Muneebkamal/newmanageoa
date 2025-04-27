<div class="modal fade" id="bundleModal" tabindex="-1" aria-labelledby="bundleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bundleModalLabel">New Order Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-sm-auto">
                                        <label for="quantity" class="col-sm-auto col-form-label">Units to Purchase</label>
                                        <div class="input-group col-sm-auto">
                                            <span class="input-group-prepend">
                                                <button type="button" data-type="minus" class="btn btn-outline-secondary btn-number">
                                                    <span class="bx bxs-minus-square"></span>
                                                </button>
                                            </span>
                                            <input type="number" name="quantity" id="quantity" class="form-control input-number" value="1" min="1">
                                            <span class="input-group-append">
                                                <button type="button" data-type="plus" class="btn btn-outline-secondary btn-number">
                                                    <span class="bx bxs-plus-square"></span>
                                                </button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <label for="listPrice" class="col-sm-auto col-form-label">List Price</label>
                                        <div class="col-sm-auto">
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.1" name="listPrice" id="listPrice" class="form-control">
                                           </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <label for="msku" class="col-sm-auto col-form-label">MSKU</label>
                                        <div class="col-sm-auto">
                                            <input type="text" name="msku" id="msku" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="col">
                                    <label for="orderNote" class="col-sm-auto col-form-label">Product/Buyer Notes</label>
                                    <div class="col-sm-auto">
                                        <textarea rows="2" name="orderNote" id="orderNote" class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="col-sm-auto">
                                    <label for="minPrice" class="col-sm-auto col-form-label">Min</label>
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.1" name="minPrice" id="minPrice" class="form-control">
                                        </div>
                                        
                                    </div>
                                </div>
                                <div class="col-sm-auto">
                                    <label for="maxPrice" class="col-sm-auto col-form-label">Max</label>
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.1" value="0" name="maxPrice" id="maxPrice" class="form-control">
                                        </div>
                                     
                                    </div>
                                </div>
                            </div>
                            <div class="text-right mt-2">
                                <button type="button" class="btn btn-danger float-end me-2" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                                <button type="button" class="btn btn-primary float-end me-2 saveOrderBtn">Add to New Order</button>

                            </div>
                        </div>

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
                                    <label for="asin" class="col col-form-label">ASIN <a class="text-info" id="amazonUrl" href="" target="_blank">Find on Amazon</a></label>
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
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary saveOrderBtn">Add to New Order</button>
                <button type="button" class="btn btn-danger"data-bs-dismiss="modal" aria-label="Close" >Cancel</button>
            </div>
        </div>
    </div>
</div>
