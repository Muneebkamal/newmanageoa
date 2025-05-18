<div class="modal fade" id="buyListLeadModal" tabindex="-1" aria-labelledby="bundleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bundleModalLabel">New Order Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="overflow-y:auto;">
                <div class="row">
                    <div class="col-md-4">
                        <input type="hidden" name="buyId" id="buyId" value="1">
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
                    <div class="col-md-4">
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
                    <div class="col-md-4">
                        <div class="col">
                            <label for="msku" class="col-sm-auto col-form-label">MSKU</label>
                            <div class="col-sm-auto">
                                <input type="text" name="msku" id="msku" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="col">
                            <label for="orderNote" class="col-sm-auto col-form-label">Product/Buyer Notes</label>
                            <div class="col-sm-auto">
                                <textarea rows="2" name="orderNote" id="orderNote" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="col-sm-auto">
                            <label for="minPrice" class="col-sm-auto col-form-label">Min</label>
                            <div class="col-sm-auto">
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="minPrice" id="minPrice" class="form-control">
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="col-sm-auto">
                            <label for="maxPrice" class="col-sm-auto col-form-label">Max</label>
                            <div class="col-sm-auto">
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" value="0" name="maxPrice" id="maxPrice" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group row">
                            <div class="text-right mt-2">
                                <button type="button" class="btn btn-danger float-end ms-3" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                                <div class="btn-group buylist-group float-end">
                                    <!-- Main button that saves the selected buylist -->
                                    <button type="button" class="btn btn-primary  buylist-button" onclick="saveBuylistData()">
                                      Select Buylist
                                    </button>
                                  
                                    <!-- Dropdown toggle button to show available buylists -->
                                    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                      <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                  
                                    <!-- Dropdown menu for selecting a buylist -->
                                    <ul class="dropdown-menu buylist-dropdown">
                                      <!-- Buylist options will be appended here dynamically -->
                                    </ul>
                                  </div>
                                  

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
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="asin" class="col col-form-label">ASIN <a class="text-info" id="amazonUrl" href="" target="_blank">Amazon Link</a></label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="orderAsin" id="orderAsin" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="category" class="col-sm-2 col-form-label">Category</label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="orderCategory" id="orderCategory" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="supplier" class="col-sm-2 col-form-label">Supplier</label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="orderSupplier" id="orderSupplier" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="sourceUrl" class="col-sm-auto col-form-label">Source URL <a href="" target="_blank" class="source_url text-info">Link to source</a></label>
                                    <div class="col-sm-auto">
                                       <div class="input-group">
                                            <span class="input-group-text">#</span>
                                            <input type="url" name="orderSourceUrl" id="orderSourceUrl" class="form-control">
                                       </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="productNote" class="col-sm-auto col-form-label">Lead Note</label>
                                    <div class="col-sm-auto">
                                        <textarea rows="2" name="orderProductNote" id="orderProductNote" class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="coupon_code" class="col-sm-auto col-form-label">Coupon Code</label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="coupon_code" id="coupon_code" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="cost" class="col-sm-auto col-form-label">Cost</label>
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.1" name="orderCost" id="orderCost" class="form-control" value="0.00">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="sellingPrice" class="col-sm-auto col-form-label">Selling Price</label>
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.1" name="orderSellingPrice" id="orderSellingPrice" class="form-control" value="0.00">
                                       </div>
                                        
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3 mt-2">
                                        <label for="net_profit">Net Profit</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="orderNetprofit" name="orderNetProfit">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3 mt-2">
                                        <label for="net_profit">Quantity</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="orderQuantity" name="orderQuantity" max="1">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="orderBsr" class="col-sm-auto col-form-label">90 Day Avg</label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="orderBsr" id="orderBsr" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="promo" class="col-sm-auto col-form-label">Promo</label>
                                    <div class="col-sm-auto">
                                        <input type="text" name="orderPromo" id="orderPromo" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">
                                   
                                    
                                    

                                    
                                    <div class="summary-box col-sm-auto mt-2">
                                        <table>
                                            <tr>
                                                <td><strong>Total Cost:</strong></td>
                                                <td><span id="Orderqty_cost"></span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Total Selling Price:</strong></td>
                                                <td><span class="ms-`" id="Orderqty_selling"></span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Gross Profit:</strong></td>
                                                <td><span id="Orderqty_profit"></span></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    
                                    
                                    
                                    
                                   
                                   
                                   
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
                
                  <div class="btn-group buylist-group float-end">
                    <!-- Main button that saves the selected buylist -->
                    <button type="button" class="btn btn-primary  buylist-button" onclick="saveBuylistData()">
                      Select Buylist
                    </button>
                  
                    <!-- Dropdown toggle button to show available buylists -->
                    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                      <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                  
                    <!-- Dropdown menu for selecting a buylist -->
                    <ul class="dropdown-menu buylist-dropdown">
                      <!-- Buylist options will be appended here dynamically -->
                    </ul>
                  </div>
                  
                <button type="button" class="btn btn-danger ms-2" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
            </div>
        </div>
    </div>
</div>
