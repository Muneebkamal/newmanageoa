<div class="modal fade" id="addBundleCreateOrder" tabindex="-1" role="dialog" aria-labelledby="addAttachmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bundleModalLabel">Add Bundled Items to New <i class="fa fa-first-order" aria-hidden="true"></i></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"  style="max-height: 500px; overflow-y: auto;">
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
                                        <label for="editListPrice" class="col-form-label">List Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.1" id="editListPrice" class="form-control">
                                        </div>
                                    </div>
                                </div>
        
                                <div class="row">
                                    <div class="col">
                                        <label for="editMsku" class="col-form-label">MSKU</label>
                                        <input type="text" id="editMsku" class="form-control">
                                    </div>
                                </div>
                            </div>
        
                            <div class="col-md-6">
                                <div class="col">
                                    <label for="editOrderNote" class="col-form-label">Bundle Notes <i aria-hidden="true" class="fa fa-question-circle-o"></i></label>
                                    <textarea rows="2" id="editOrderNote" class="form-control"></textarea>
                                </div>
                                
                                <div class="col-sm-auto">
                                    <label for="editMinPrice" class="col-form-label">Min</label>
                                    <input type="text" id="editMinPrice" class="form-control">
                                </div>
                                
                                <div class="col-sm-auto">
                                    <label for="editMaxPrice" class="col-form-label">Max</label>
                                    <input type="text" id="editMaxPrice" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <h5>Bundled Lead Info</h5>
                <form class="form-group justify-content-center">
                    <div class="form-group row">
                        <div class="col">
                            <label for="editNameCreateOrderItem" class="col-form-label">Name</label>
                            <textarea rows="1" id="editNameCreateOrderItem" class="form-control"></textarea>
                        </div>
                    </div>
        
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="editAsinCreateOrderItem" class="col-form-label">ASIN <a href="" target="_blank">Amazon Link</a></label>
                            <input type="text" id="editAsinCreateOrderItem" class="form-control">
                            
                            <label for="editCategoryCreateOrderItem" class="col-form-label">Category</label>
                            <input type="text" id="editCategoryCreateOrderItem" class="form-control">
                            
                            <label for="editSupplierCreateOrderItem" class="col-form-label">Supplier</label>
                            <div>Multiple (Bundle)</div>
        
                            <label for="editProductNoteCreateOrderItem" class="col-form-label">Lead Note</label>
                            <textarea rows="2" id="editProductNoteCreateOrderItem" class="form-control"></textarea>
                        </div>
        
                        <div class="col-md-6">
                            <label for="editCostCreateOrderItem" class="col-form-label">Cost</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.1" id="editCostCreateOrderItem" class="form-control">
                            </div>
                            
                            
                            <label for="editSellingPriceCreateOrderItem" class="col-form-label">Selling Price</label>
                            <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.1" id="editSellingPriceCreateOrderItem" class="form-control">
                            </div>
                            
                            <label for="editNinetyDayAverageCreateOrderItem" class="col-form-label">90 Day Avg</label>
                            <input type="number" id="editNinetyDayAverageCreateOrderItem" class="form-control">
        
                            <label for="editItemFlags" class="col-form-label">Lead Flags</label>
                            <br>
                            <div id="editItemFlags" role="group" aria-label="Item toggles" class="btn-group">
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
                </form>
                <div class="row float-end">
                    <div class="text-right float-end mb-2">
                        <button type="button" class="btn btn-primary" onclick="saveOrderBundleItems()">
                            Add Bundle to New Order
                        </button>   
                        <button type="button" class="btn btn-danger float-end ms-2" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
                <br>
                <br>
                <hr>
                <div class="row">
                    <h4>Source Product Info</h4>
                    <div class="col" id="bundleSection"></div>
                </div>
            </div>
            <hr>
            <div class="modal-footer">
                <div class="button-group text-center mt-4">
                    <button id="prevBtn" class="btn btn-secondary" style="display: none;">
                        Previous  Source Item
                    </button>                    
                    <button id="nextBtn" class="btn btn-primary">
                        Next Source Item
                    </button>
                    <button type="button" id="addToBuyListBtn" class="btn btn-primary" onclick="saveOrderBundleItems()">
                        Add Bundle to New Order
                    </button>                   
                    <button type="button" class="btn btn-danger float-end ms-2" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>