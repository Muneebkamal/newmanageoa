<div class="modal fade" id="exampleModalScrollable" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalScrollableTitle">Add Lead</h5>
                <h5 class="modal-title d-none" id="exampleModalScrollableTitle1">Edit Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="lead-form">
                    @csrf
                    <input type="hidden" id="lead_id" name="lead_id">
                    <div id="lead-add-time" class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="">Add Bundle?</label><br>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="bundle" name="bundle"
                                        id="bundle">
                                </div>

                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="">Lead List Source*</label>
                                <div class="d-flex align-items-center">
                                    <select id="lead-source-list" name="source_id" class="form-select lead-source-list2"
                                        required>

                                    </select>
                                    <b class="ms-2">+</b>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="">Publish Date</label>
                                <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>">

                            </div>
                        </div>
                    </div>
                    <div id="lead-update-time" class="row d-none">
                        <div class="col-md-12 d-flex align-items-center">
                            <h3>Published to </h3>
                            <h5 class="m-0"><span id="lead-source-trans" class="badge bg-primary mx-2"></span></h5>

                            <button type="button" class="btn btn-light p-0 px-1 me-2" onclick="sourceTransfer()">
                                <i class="ri-edit-box-line fs-5"></i>
                            </button> on
                            <h5 class="m-0"><span id="lead-date-update" class="badge bg-dark mx-2"></span></h5>
                            <h5 class="mb-0" >Created By: <span id="created_by_name"></span> </h5>
                        </div>
                        <div class="col-md-8 d-none transfer-source">
                            <div class="mb-3">
                                <label for="">Transfer item List</label>
                                <div class="d-flex align-items-center">
                                    <select name="update_source_id" class="form-select lead-source-list" required>

                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-none transfer-source">
                            <button type="button" class="btn btn-light mt-4" onclick="sourceTransfer()">Cancle
                                Transfer</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="">Item Name*</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="">ASIN* <a class="text-info amazonUrl"  href="#">Find on Amazon</a></label>
                                <input type="text" class="form-control" id="asin" name="asin" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cost">Cost*</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.1" class="form-control" id="cost" name="cost" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="">Category*</label>
                                <input type="text" class="form-control" id="category" name="category" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sell_price">Selling Price*</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.1" class="form-control" id="sell_price" name="sell_price" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="">Supplier</label>
                                <input type="text" class="form-control" id="supplier" name="supplier" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="net_profit">Net Profit</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="net_profit" name="net_profit">
                                </div>
                            </div>
                        </div>                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="url">
                                    Source URL* 
                                    <a href="#" class="text-info source_url">Link to source</a>
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="url" name="url" placeholder="www.example.com" required>
                                </div>
                            </div>
                        </div>                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="">ROI (<span id="roi_display">0.00%</span>)</label>
                                <input type="number"  step="0.1" class="form-control" id="item_roi" name="item_roi">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="">90 Day BSR</label>
                                <input type="text" class="form-control" id="item_bsr" name="item_bsr">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="">Quantity</label>
                                <input type="number" class="form-control" id="quantity_new" name="quantity" value="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="">Promo</label>
                                <input type="text" class="form-control" id="promo" name="promo">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="">Currency</label>
                                <input type="text" class="form-control" id="currency" value="USD" name="currency">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="">Coupon Code</label>
                                <input type="text" class="form-control" id="coupon" name="coupon">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="">Lead Notes</label>
                                <textarea type="text" class="form-control" id="notes" name="notes"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="">Item Flags</label>
                                <div class="d-flex">
                                    <div class="form-check me-2">
                                        <input class="form-check-input" type="checkbox" id="is_hazmat"
                                            name="is_hazmat">
                                        <label class="form-check-label" for="is_hazmat">
                                            Hazmat
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_disputed"
                                            name="is_disputed">
                                        <label class="form-check-label" for="is_disputed">
                                            Disputed
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="summary-box">
                                <div class="card mb-3" id="summaryBox">

                                </div>
                                {{-- <table>
                                    <tr>
                                        <td><strong>Total Cost:</strong></td>
                                        <td><span id="qty_cost"></span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Selling Price:</strong></td>
                                        <td><span class="ms-`" id="qty_selling"></span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Gross Profit:</strong></td>
                                        <td><span id="qty_profit"></span></td>
                                    </tr>
                                </table> --}}
                            </div>
                        </div>
                        
                    </div>
                </form>

                <div id="source-items-section" class="row w-100 d-none">
                    <hr>
                    <div class="col-md-12 mt-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="m-0">Bundled Lead Info</h4>
                            <button type="button" class="btn btn-outline-primary" id="add-source-item">Add
                                Source Item</button>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <span id="source-item-count">Source Item 0 of 0</span>
                    </div>
                    <div id="source-items-container"></div>
                    <div class="col-md-12 mt-3">
                        <button type="button" id="prev-item"
                            class="btn btn-outline-secondary d-none">Previous</button>
                        <button type="button" id="next-item" class="btn btn-outline-secondary d-none">Next</button>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                {{-- <button type="button" class="btn btn-primary">Save and add to Buy List</button> --}}
                <button type="button" class="btn btn-primary" id="add-lead">Save</button>
                <button type="button" class="btn btn-success d-none" id="update-lead">Update</button>
                <button type="button" class=" btn btn-danger" data-bs-dismiss="modal" aria-label="Close">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

