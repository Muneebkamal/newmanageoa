<div class="modal fade" id="editBuyListLeadModal" tabindex="-1" aria-labelledby="editBundleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header  d-flex justify-content-between">
                <h5 class="modal-title ms-2 me-2" id="editBundleModalLabel"> <span>Order Item Information</span>
                   
                </h5>
                <div>
                    <span> <strong>Created By:</strong> <span id="createdBy"></span> </span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="itemIdEdit" id="itemIdEdit">
                    <input type="hidden" id="modalMode" name="modal_mode" value="edit">

                    <div class="col">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="col-sm-auto">
                                    <label for="editQuantity" class="col-sm-auto col-form-label">Units to Purchase</label>
                                    <div class="input-group col-sm-auto">
                                        <span class="input-group-prepend">
                                            <button type="button" data-type="minus" class="btn btn-outline-secondary btn-number" onclick="changeQty('minus')">
                                                <span class="bx bxs-minus-square"></span>
                                            </button>
                                        </span>
                                        <input type="number" name="editQuantity" id="editQuantity" class="form-control input-number" value="1" min="1" onchange="calculateValues(this.value)">
                                        <span class="input-group-append">
                                            <button type="button" data-type="plus" class="btn btn-outline-secondary btn-number" onclick="changeQty('plus')">
                                                <span class="bx bxs-plus-square"></span>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="col">
                                    <label for="editListPrice" class="col-sm-auto col-form-label">List Price</label>
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.1" name="editListPrice" id="editListPrice" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="col">
                                    <label for="editMsku" class="col-sm-auto col-form-label">MSKU</label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="editMsku" id="editMsku" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="col">
                                    <label for="editOrderNote" class="col-sm-auto col-form-label">Product/Buyer Notes</label>
                                    <div class="col-sm-auto">
                                        <textarea rows="2" name="editOrderNote" id="editOrderNote" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="col-sm-auto">
                                    <label for="editMinPrice" class="col-sm-auto col-form-label">Min</label>
                                    <div class="col-sm-auto">
                                        <input type="number" name="editMinPrice" id="editMinPrice" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="col-sm-auto">
                                    <label for="editMaxPrice" class="col-sm-auto col-form-label">Max</label>
                                    <div class="col-sm-auto">
                                        <input type="number" value="0" name="editMaxPrice" id="editMaxPrice" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="text-end mt-2">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary save-button" id="saveEditedLeadButton" onclick="updateTheLead();">Save</button>
                            </div>
                        </div>

                        <h5>Lead Information</h5>
                        <form class="form-group justify-content-center">
                            <div class="form-group row">
                                <div class="col">
                                    <label for="editLeadName" class="col-sm-2 col-form-label">Name</label>
                                    <div class="col-sm-auto">
                                        <textarea rows="2" name="editOrderName" id="editOrderName" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="editAsin" class="col col-form-label">ASIN <a class="text-info" id="editAmazonUrl" href="" target="_blank">Find on Amazon</a></label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="editOrderAsin" id="editOrderAsin" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="editCategory" class="col-sm-2 col-form-label">Category</label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="editOrderCategory" id="editOrderCategory" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="editSupplier" class="col-sm-2 col-form-label">Supplier</label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="editOrderSupplier" id="editOrderSupplier" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="editSourceUrl" class="col-sm-auto col-form-label">Source URL <a href="" target="_blank" class="edit_source_url text-info">Link to source</a></label>
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <span class="input-group-text">#</span>
                                            <input type="url" name="editOrderSourceUrl" id="editOrderSourceUrl" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="editProductNote" class="col-sm-auto col-form-label">Lead Note</label>
                                    <div class="col-sm-auto">
                                        <textarea rows="2" name="editOrderProductNote" id="editOrderProductNote" class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="editCouponCode" class="col-sm-auto col-form-label">Coupon Code</label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="editCouponCode" id="editCouponCode" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="editCost" class="col-sm-auto col-form-label">Cost</label>
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.1" name="editOrderCost" id="editOrderCost" class="form-control" value="0.00">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="editOrderSellingPrice" class="col-sm-auto col-form-label">Selling Price</label>
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.1" name="editOrderSellingPrice" id="editOrderSellingPrice" class="form-control" value="0.00">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="editOrderNetProfit" class="col-sm-auto col-form-label">Net Profit</label>
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.1" name="editOrderNetProfit" id="editOrderNetProfit" class="form-control" value="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="quantity" class="col-sm-auto col-form-label">Quantity</label>
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="1" name="quantity" id="quantity" class="form-control" value="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="editOrderBsr" class="col-sm-auto col-form-label">90 Day Avg</label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="editOrderBsr" id="editOrderBsr" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="editPromo" class="col-sm-auto col-form-label">Promo</label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="editOrderPromo" id="editOrderPromo" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="summary-box col-sm-auto mt-2">
                                        <table>
                                            <tr>
                                                <td><strong>Total Cost:</strong></td>
                                                <td><span class="order-qty-cost" id="Orderqty_cost"></span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Total Selling Price:</strong></td>
                                                <td><span class="order-qty-selling ms-`" id="Orderqty_selling"></span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Gross Profit:</strong></td>
                                                <td><span class="order-qty-gross-profit" id="Orderqty_profit"></span></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="editItemFlags" class="col-sm-auto col-form-label">Lead Flags</label>
                                    <div class="col-sm-auto" id="editItemFlags">
                                        <div role="group" aria-label="Item toggles" class="btn-group">
                                            <button type="button" class="btn btn-light">
                                                <div class="form-check me-2">
                                                    <input class="form-check-input" type="checkbox" id="editOrderIsHazmat" name="editOrderIsHazmat">
                                                    <label class="form-check-label" for="editOrderIsHazmat">Hazmat</label>
                                                </div>
                                            </button>
                                            <button type="button" class="btn btn-light">
                                                <div class="form-check me-2">
                                                    <input class="form-check-input" type="checkbox" id="editOrderIsDisputed" name="editOrderIsDisputed">
                                                    <label class="form-check-label" for="editOrderIsDisputed">Disputed</label>
                                                </div>
                                            </button>
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4"></div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">
                                   
                                    
                                    
                                    
                                    


                                </div>
                            </div>
                            <div class="form-group-row">
                                <div class="col-md-12">
                                    
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger d-none" id="rejectedBtn" onclick="updateTheLead('reject');"> Reject </button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary save-button" id="saveEditedLeadButton" onclick="updateTheLead();">Save</button>
            </div>
        </div>
    </div>
</div>
