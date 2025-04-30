@section('script')
<!-- Dropzone.js CSS CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css" integrity="sha512-aXjtrYtbLgHRzkP2yNUOeeM3JidDPM4pewQhZJZpEppT9dFMjWEYhz2hE7R2RD1fdf2fVByjDcfTe+hWiWaz+g==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- jQuery CDN (optional, if you need jQuery for other parts) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Dropzone.js JS CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js" integrity="sha512-v2EEHXClYWUqDgPTBQaNEksVffxC5aRyIR+09tHbyyBDReNhiwDQ6V2kq0vdVZb9kQm1uUyrGmxS6KoVNPdvg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
 <script>
    var orderId = $('#order_id').val();
    var orderassin = {!! json_encode($order->asin) !!};
    var items = [];
    var grandTotalAll = 0;
    // Event listener for changes in discount, shipping cost, or sales tax
    $('#per_tax_discount, #post_tax_discount, #shipping_cost, #sales_tax').on('input', function() {
        calculateGrandTotal();
    });
    loadOrderItems(orderId)
    setTimeout(() => {
        calculateGrandTotal()
    }, 500);
    $('#buy_cost_type').on('change',function(){
        if($(this).val() == 'sku'){
            $('.total_cost').removeClass('d-none')
            $('.single_cost').addClass('d-none')
        }else{
            $('.total_cost').addClass('d-none')
            $('.single_cost').removeClass('d-none')
        }
    })
    // Dropzone initialization
    Dropzone.autoDiscover = false;

    const myDropzone = new Dropzone("#imageDropzone", {
        url: "{{ url('orderattachments/upload') }}",  // Replace this with your upload URL
        maxFiles: 10,  // Set the maximum number of files allowed
        maxFilesize: 2,  // Maximum file size in MB
        acceptedFiles: "image/*",  // Accept only images
        addRemoveLinks: true,  // Show a remove link for each file
        dictDefaultMessage: "Drag & Drop images here or click to upload",
        autoProcessQueue: false,  // Disable automatic upload, control it manually
        init: function () {
            let dzInstance = this;

            // Event when file is added to Dropzone
            this.on("addedfile", function (file) {
                console.log("File added:", file.name);

                // Set the file name in the input field
                document.getElementById("createDisplayName").value = file.name;
            });

            // Event when file is removed from Dropzone
            this.on("removedfile", function (file) {
                console.log("File removed:", file.name);

                // Clear the input field when a file is removed
                document.getElementById("createDisplayName").value = '';
            });

            // Save files on "Save" button click
            document.getElementById("saveFiles").addEventListener("click", function () {
                // Check if there are files queued for upload
                if (dzInstance.getQueuedFiles().length === 0) {
                    alert("No files to upload!");
                    return;
                }

                // Validate the note and file name
                const displayName = document.getElementById("createDisplayName").value;
                const note = document.getElementById("createNote").value;
                if (!displayName) {
                    alert("Please fill out both the file name and note.");
                    return;
                }

                // Manually trigger the file upload queue
                dzInstance.processQueue();
            });

            // Add extra data before the file is sent to the server
            this.on("sending", function (file, xhr, formData) {
                // Get values from the modal inputs
                let displayName = document.getElementById("createDisplayName").value;
                let note = document.getElementById("createNote").value;

                // Append extra data to the formData object
                formData.append("display_name", displayName);
                formData.append("note", note);
                formData.append("order_id", orderId);
                let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                formData.append("_token", token); 
            });

            // On successful file upload
            this.on("success", function (file, response) {
                dzInstance.removeFile(file);  // Remove file from Dropzone after successful upload
                $('#addAttachmentModal').modal('hide');
                fetchFiles();
                toastr.success('File uploaded successfully!'); 
                console.log("File uploaded successfully:", file.name);
            });

            // On file upload error
            this.on("error", function (file, errorMessage) {
                console.error("File upload error:", errorMessage);
            });
        }
    });
   
   
    $('#addNewOrderItemBtn').on('click', function() {
        var newItemRow = `
            <tr class="new-item-row">
            <td>
                <input type="text" name="asin" class="form-control text-center" placeholder="" required>
            </td>
            <td>
                <input type="text" name="name" class="form-control text-center" placeholder="" required>
            </td>
            <td>
                <input type="number" name="units" min="0" step="1" class="form-control text-center" placeholder="# of Units" value="1" required>
            </td>
            <td class="single_cost d-none">
                <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" name="buy_cost" min="0" step=".01" class="form-control text-center" placeholder="" value="0" required>
                </div>
            </td>
            <td>
                <div class="input-group total_cost">
                <span class="input-group-text">$</span>
                <input type="number" name="sku_total" min="0" step=".01" class="form-control text-center" placeholder="" value="0" required>
                </div>
            </td>
            <td class="bg-light">
                <div class="col d-flex justify-content-between text-center">
                    <button type="button" class="btn btn-sm btn-primary save-item-btn">Save Item</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearInputs(this)">
                        <i aria-hidden="true" class="ri-close-circle-line"></i>
                    </button>
                </div>
            </td>
            </tr>
        `;
        
        $('#orderInputsContainer').append(newItemRow);
        $('#addNewOrderItemBtn').hide();  // Hide add button until the item is saved
    });
    var sale_text_rate_var = {!! json_encode($order->sales_tax_rate) !!}
    // Save item to the array and send to the server via AJAX
    $(document).on('click', '.save-item-btn', function() {
        var parentRow = $(this).closest('.new-item-row');
        var asin = parentRow.find('input[name="asin"]').val(); // Assuming input has name="asin"
        var name = parentRow.find('input[name="name"]').val(); // Assuming input has name="name"
        var units = parentRow.find('input[name="units"]').val(); // Assuming input has name="units"
        var cost = parentRow.find('input[name="sku_total"]').val(); // Assuming input has name="cost"
        var orderId = $('#order_id').val(); // Assuming you have an order_id field
        // Validate the inputs before saving
        if (asin && name) {
            // Push the data to the items array
            var itemData = {
                asin: asin,
                name: name,
                units: units,
                cost: cost,
                order_id: orderId
            };
            // Send the data via AJAX to save in the database
            $.ajax({
                url: "{{ url('save-order-item') }}",  // Replace with your actual URL
                method: 'POST',
                data: itemData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                success: function(response) {
                    // Assuming the response contains the saved items
                    if (response.success) {
                        items = response.items;
                        parentRow.remove();
                        $('#addNewOrderItemBtn').show();
                        loadOrderItems(orderId)
                        setTimeout(() => {
                            calculateGrandTotal()
                        }, 1000);
                    } else {
                        alert('Error saving item: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    alert('An error occurred while saving the item.');
                }
            });
        } else {
            alert("Please fill out all fields.");
        }
    });
    // Function to calculate grand total
    function calculateGrandTotal() {
        let subtotal = grandTotalAll;
        // console.log(subtotal);

        // Get additional costs from the form inputs
        let preTaxDiscount = parseFloat($('#per_tax_discount').val()) || 0;
        let postTaxDiscount = parseFloat($('#post_tax_discount').val()) || 0;
        let shippingCost = parseFloat($('#shipping_cost').val()) || 0;
        let salesTax = parseFloat($('#sales_tax').val()) || 0;

        // Apply discounts and tax
        let preTaxSubtotal = subtotal - preTaxDiscount;
        let grandTotal = preTaxSubtotal + shippingCost + salesTax - postTaxDiscount;
        

        // Update the UI
        $('#buyCostCalcPreTaxSubtotal').text(`$${preTaxSubtotal.toFixed(2)}`);
        $('#buyCostCalcGrandTotal').html(`<h5>$${grandTotal.toFixed(2)}</h5>`);
        $('#buyCostCalcSalesTaxPercentage').html(`<h5>$${salesTax.toFixed(2)}</h5>`);
    }

    // Function to retrieve and display all line items for the given order_id
    function loadOrderItems(orderId) {
        $.ajax({
            url: "{{ url('/get-order-items') }}",  // Replace with your actual URL
            method: 'GET',
            data: { order_id: orderId },  // Pass the order_id as a query parameter
            success: function(response) {
                if (response.success) {
                    // Update the items array with the retrieved items
                    items = response.items;

                    let productTableBody = $('#product-table-body');
                    let productCount = $('#product-count');
                    productTableBody.empty();
                    
                    items.forEach(product => {
                    let percentage = sale_text_rate_var;
                    let value = product.sku_total;
                    let totalSalesTax = (percentage / 100) * value;
                    var totalNewSkuTotal = product.sku_total + totalSalesTax;
                    var newBuyCost = totalNewSkuTotal / product.unit_purchased;
                    let cost = parseFloat(newBuyCost) || 0;
                    let sellingPrice = parseFloat(product.list_price) || 0;
                    let quantity = parseInt(product.unit_purchased) || 1;

                    let totalItemCost = cost * quantity;
                    let totalItemSelling = sellingPrice * quantity;
                    let itemProfit = totalItemSelling - totalItemCost;
                    let itemProfitPerPiece =itemProfit/ quantity;

                    // Create product summary card dynamically
                    let productSummaryCard = `
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="summary-box">
                                    <table class="table table-bordered text-center">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Qty</th>
                                                <th>Selling Price</th>
                                                <th>Cost Price</th>
                                                <th>Gross Profit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Total</strong></td>
                                                <td><strong>${quantity}</strong></td>
                                                <td><span class="total_selling_price">$${totalItemSelling.toFixed(2)}</span></td>
                                                <td><span class="total_cost_price">$${totalItemCost.toFixed(2)}</span></td>
                                                <td><span class="total_gross_profit">$${itemProfit.toFixed(2)}</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Per Pcs</strong></td>
                                                <td><strong></strong></td>
                                                <td><span class="perpcs_selling_price">$${sellingPrice.toFixed(2)}</span></td>
                                                <td><span class="perpcs_cost_price">$${cost.toFixed(2)}</span></td>
                                                <td><span class="perpcs_gross_profit">$${itemProfitPerPiece.toFixed(2)}</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#appendDataProfit').append(productSummaryCard);

                        console.log(product)
                        var orderedItem = product.total_units_purchased != 0 ? `<span class="badge bg-dark" title="Ordered">${product.total_units_purchased}</span>` : '<span title="Ordered">-</span>'
                        var receivedItems = product.total_units_received != 0 ? `<span class="badge bg-info" title="received" id="itemTotalReceived${product.id}">${product.total_units_received}</span>` : `<span title="received" id="itemTotalReceived${product.id}">-</span>`
                        var shippedItems = product.total_units_shipped != 0 ? `<span class="badge bg-success" title="Shipped" id="itemTotalShipped${product.id}">${product.total_units_shipped}</span>` : `<span title="Shipped" id="itemTotalShipped${product.id}">-</span>`
                        var errorItems = product.unit_errors != 0 ? `<span class="badge bg-danger" title="Error" id="itemTotalError${product.id}">${product.unit_errors}</span>` : '<span title="Error" id="itemTotalError'+product.id+'">-</span>';
                        
                       
                        let row = `<tr>
                            <td>
                                ${product.is_disputed ? `<i class=" ri-haze-2-line text-warning ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Item data may be disputed"></i>
                                ` : ''}
                                ${product.is_hazmat === 1 ? `<i class=" ri-alarm-warning-fill text-danger ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Hazmat item"></i>
                                ` : ''}
                                <a target="_blank" href="${product.source_url}" title="${product.name}" 
                                style="display: inline-block; width: 150px; word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">
                                    ${product.name}
                                </a>

                            </td>
                            <td class="d-flex justify-content-between">
                                <a href="https://www.amazon.com/dp/${product.asin}" target="_blank">${product.asin}</a>
                                <i class="ri-file-copy-line ms-2" style="cursor: pointer;" onclick="copyToClipboard('${product.asin}')" title="Copy ASIN"></i>
                            </td>

                            <td><input type="number" value="${product.unit_purchased}" class="form-control form-control-sm"></td>
                             <!-- Buy Cost Column with Editable Input -->
                            <td ondblclick="showEditableInput(this)">
                                <span class="display-text">$${parseFloat(newBuyCost || 0).toFixed(2)}</span>
                                <input type="number"step="0.1"  class="edit-input form-control form-control-sm" value="${parseFloat(newBuyCost || 0).toFixed(2)}" style="display:none;" onblur="updateValue(this, 'buy_cost')">
                            </td>
                            <!-- SKU Total Column with Editable Input -->
                            <td ondblclick="showEditableInput(this)">
                                <span class="display-text">$${parseFloat(totalNewSkuTotal|| 0).toFixed(2)}</span>
                                <input type="number" step="0.1" class="edit-input form-control form-control-sm" value="${parseFloat(totalNewSkuTotal || 0).toFixed(2)}" style="display:none;" onblur="updateValue(this, 'sku_total')">
                            </td>
                            <td>${orderedItem} ${receivedItems} ${shippedItems} ${errorItems}</td>
                             <!-- Product Buyer Notes with Editable Textarea -->
                            <td ondblclick="showEditableTextarea(this)">
                                <span class="display-text">${product.product_buyer_notes || 'Add Notes Here'}</span>
                                <textarea class="edit-textarea form-control form-control-sm" style="display:none;" onblur="updateValue(this, 'product_buyer_notes')">${product.product_buyer_notes || ''}</textarea>
                            </td>
                            <td>${product.upc || '-'}</td>
                            <td>${product.msku || '-'}</td>
                            <td>$${product.list_price.toFixed(2)}</td>
                            <td>$${product.min.toFixed(2)}</td>
                            <td>$${product.max.toFixed(2)}</td>
                            <td class="text-center align-middle">
                                <div class="d-flex flex-column justify-content-between align-items-center h-100" style="">
                                <div class="d-flex justify-content-end">
                                    <div class="btn-group" style="max-height: 30px;">
                                        <!-- View Button -->
                                        <button id="toggleBtn-${product.id}" class="btn btn-sm btn-outline-primary px-2" title="View" onclick="getErrorLogs(${product.id})">
                                            <i id="icon-${product.id}" class="ri-eye-fill" aria-hidden="true"></i>
                                        </button>

                                        <!-- Create Event Dropdown -->
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                Create Event
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                                <li>
                                                    <button type="button" class="dropdown-item" data-product-id="${product.id}" onclick="popWorkOrders(${product.id})">
                                                        Send To Prep Work Order
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item" data-product-id="1" onclick="handleDropdownClick(${product.id},'replace')">
                                                        Replacement
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item" data-product-id="1" onclick="handleDropdownClick(${product.id},'return')">
                                                        Return for Refund
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item" data-product-id="1" onclick="handleDropdownClick(${product.id},'received')">
                                                        Never Received
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- More Actions Dropdown -->
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical" aria-hidden="true"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                                <li><button type="button" class="dropdown-item btn-outline-info" onclick="editItem(${product.id})">
                                                    <i class=" ri-pencil-fill text-info"></i> Edit
                                                </button></li>
                                                <li><button type="button" class="dropdown-item btn-outline-primary" onclick="copyNSlpitItem(${product.id})">
                                                    <i class=" ri-file-copy-line text-info"></i> Copy/Split Order Item
                                                </button></li>
                                                
                                                <li><button type="button" class="dropdown-item btn-outline-success" onclick="copuItemBuylist(${product.id})">
                                                    <i class="ri-money-dollar-box-line text-success"></i> Copy <span>Item</span> to Buylist
                                                </button></li>
                                                <li><button type="button" class="dropdown-item btn-outline-danger" onclick="deleteITem(${product.id})">
                                                    <i class=" ri-delete-bin-line text-danger "></i> Delete
                                                </button></li>
                                                <li><button type="button" class="dropdown-item btn-outline-danger" onclick="rejectItem(${product.id})">
                                                    <i class="ri-forbid-2-line text-danger btn-outline-danger"></i> Reject
                                                </button></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-success mt-2 btn-sm" data-product-id="${product.id}" onclick="popWorkOrders(${product.id})">
                                    Send To Prep Work Order
                                </button>
                                </div>
                            </td>
                        </tr>
                        <tr id="shipping_fba_row_${product.id}" class="d-none">
                           <td colspan="13">
                                <div class="card pb-1 mx-1">
                                    <div class="card-header">
                                        <h6>Ship to FBA Event</h6>
                                    </div>
                                    <div class="card-body">
                                        <form id="shippingForm${product.id}">
                                            <div class="row justify-content-center">
                                                <div class="col-md-1">
                                                    <label for="shippingBatch" class="form-label">Shipping Batch 
                                                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                                                    </label>
                                                    <select id="shippingBatch${product.id}" name="shipping_batch" class="form-select"></select>
                                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2 w-100" onclick="openShippingModal(${product.id})">Create New Shipping Batch</button>
                                                </div>
                                                <div class="col-md-1">
                                                    <label for="itemsShipped" class="form-label"># To Ship 
                                                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                                                    </label>
                                                    <input type="number" id="itemsShipped${product.id}" class="form-control" name="items" min="0" step="1" oninput="checkthisinput(${product.id})">
                                                </div>
                                                <div class="col-md-2">
                                                    <label for="qcCheck" class="form-label">QC Check 
                                                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                                                    </label>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn btn-outline-primary dropdown-toggle w-100" id="dropdownQcSelect${product.id}" data-bs-toggle="dropdown">
                                                            AMZ MATCH?
                                                        </button>
                                                         <ul class="dropdown-menu" aria-labelledby="dropdownQcSelect${product.id}">
                                                    <li>
                                                        <div class="form-check">
                                                            <input type="checkbox" id="upcMatchesFlag_${product.id}" 
                                                                name="upc_matches_flag" 
                                                                value="upc_matches" 
                                                                class="qc-checkbox${product.id} form-check-input" onchange="singleCheck(${product.id},'upcMatchesFlag_')">
                                                            <label for="upcMatchesFlag_${product.id}" class="form-check-label">UPC MATCHES</label>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="form-check">
                                                            <input type="checkbox" id="titleMatchesFlag_${product.id}" 
                                                                name="title_matches_flag" 
                                                                value="title_matches" 
                                                                class="qc-checkbox${product.id} form-check-input" onchange="singleCheck(${product.id},'titleMatchesFlag_')">
                                                            <label for="titleMatchesFlag_${product.id}" class="form-check-label">TITLE MATCHES</label>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="form-check">
                                                            <input type="checkbox" id="imageMatchesFlag_${product.id}" 
                                                                name="image_matches_flag" 
                                                                value="image_matches" 
                                                                class="qc-checkbox${product.id} form-check-input" onchange="singleCheck(${product.id},'imageMatchesFlag_')">
                                                            <label for="imageMatchesFlag_${product.id}" class="form-check-label">IMAGE MATCHES</label>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="form-check">
                                                            <input type="checkbox" id="descriptionMatchesFlag_${product.id}" 
                                                                name="description_matches_flag" 
                                                                value="description_matches" 
                                                                class="qc-checkbox${product.id} form-check-input" onchange="singleCheck(${product.id},'descriptionMatchesFlag_')">
                                                            <label for="descriptionMatchesFlag_${product.id}" class="form-check-label">DESCRIPTION MATCHES</label>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="form-check">
                                                            <input type="checkbox" id="qcCheckAll${product.id}" 
                                                                class="form-check-input" 
                                                                onchange="checkAll(${product.id})">
                                                            <label for="qcCheckAll${product.id}" class="form-check-label">Check All</label>
                                                        </div>
                                                    </li>
                                                </ul>
                                                    </div>

                                                </div>

                                                <div class="col-md-1">
                                                    <label for="expirationDate" class="form-label">Expiration Date</label>
                                                    <input type="date" id="expirationDate${product.id}" name="expire_date" class="form-control" onchange="checkDate(${product.id})">
                                                </div>
                                                <div class="col-md-1">
                                                    <label for="asinOverride" class="form-label">ASIN Override 
                                                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                                                    </label>
                                                    <input type="text" id="asinOverride${product.id}" name="asin_override" class="form-control" placeholder="ABC123" oninput="showHiddenInputs(${product.id})">
                                                </div>                                                
                                                <div class="col-md-1 d-none additionalInputs${product.id}">
                                                    <label for="productNameOverride${product.id}" class="form-label">Title Override </label>
                                                    <input type="text" id="productNameOverride${product.id}" name="product_name_override" class="form-control" placeholder="Title">
                                                    <label for="listPriceOverride${product.id}" class="form-label">List Price Override</label>
                                                    <input type="number" step="0.1" id="listPriceOverride${product.id}" name="list_price_orverride" class="form-control" placeholder="List Price">
                                                </div>
                                                <div class="col-md-1 d-none additionalInputs${product.id}">
                                                    <label for="minOverride${product.id}" class="form-label">Min Override </label>
                                                    <input type="number" step="0.1" id="minOverride${product.id}" name="min_orverride" class="form-control" placeholder="Min Price">
                                                        <label for="maxOverride${product.id}" class="form-label">Max Override</label>
                                                    <input type="number" step="0.1" id="maxOverride${product.id}" name="max_orverride" class="form-control" placeholder="Max Price">
                                                </div>
                                                   
                                                <div class="col-md-1">
                                                    <label for="condition" class="form-label">Condition</label>
                                                    <input type="text" id="condition" name="condition" class="form-control" value="new">
                                                </div>
                                                <div class="col-md-1">
                                                    <label for="product_upc" class="form-label">UPC</label>
                                                    <input type="text" name="product_upc" id="product_upc" class="form-control" placeholder="UPC" maxlength="14">
                                                </div>
                                                <div class="col-md-1">
                                                    <label for="mskuOverride" class="form-label">MSKU</label>
                                                    <input type="text" id="mskuOverride" name="msku_orderride" class="form-control" placeholder="MSKU" maxlength="40">
                                                </div>
                                                <div class="col-md-1">
                                                    <label for="shippingNotes" class="form-label">Ship to FBA Notes</label>
                                                    <textarea id="shippingNotes" name="shipping_notes" class="form-control" rows="4" placeholder="Notes"></textarea>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <!-- Warning Message on the Left Side -->
                                                <div class="col-md-8 offset-md-1 d-none" id="alertForExpireDate">
                                                    <div class="text-center alert alert-warning">
                                                        <p><strong>Warning:</strong> The Expiration Date should be at least 125 days in the future. Amazon will not accept most items that expire within 105 days of receipt. If you were to ship today, this would allow 20 days travel time.</p>
                                                    </div>
                                                </div>

                                                <!-- Buttons on the Right Side -->
                                                <div class="col-auto ms-auto mt-5">
                                                    <button type="button" class="btn btn-danger" onclick="closeTheTab(${product.id},'ship')">Cancel</button>
                                                    <button id="saveShippingBtn${product.id}" type="button" class="btn btn-primary" disabled onclick="saveShippingevent(${product.id},'ship')">Create Shipping Event</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>                                
                        </tr>
                        <tr  id="replacement_row_${product.id}" class="d-none">
                            <td colspan="13">
                                <div class="card pb-1">
                                <div class="card-header">
                                    <h6>Replacement Event</h6>
                                    </div>
                                <div class="card-body">
                                    <form id="replaceForm${product.id}">
                                        <div class="row">
                                            <div class="col-12 col-md-2 mb-3">
                                                <label for="replacementQty" class="my-2"># of Items</label>
                                                <input type="number" id="replacementQty" name="item_quantity" min="0" step="1" class="form-control">
                                            </div>
                                            <div class="col-12 col-md-1 mb-3">
                                                <label for="receivedItem${product.id}" class="my-2">Received?</label> <br>
                                                <button type="button" class="btn btn-light">
                                                    <div class="form-check me-2">
                                                        <input class="form-check-input" type="checkbox" id="receivedItem${product.id}" name="received">
                                                        <label class="form-check-label" for="receivedItem${product.id}"><i aria-hidden="true" class="fa fa-square-o"></i> Yes</label>
                                                    </div>
                                                </button>
                                            </div>
                                            <div class="col-12 col-md-2 mb-3">
                                                <label for="replacementTrackingNumber" class="my-2">Tracking Number</label>
                                                <input type="text" id="replacementTrackingNumber" name="tracking_number" placeholder="Tracking #" class="form-control">
                                            </div>
                                            <div class="col-12 col-md-2 mb-3">
                                                <label for="replacementSupplierNotes" class="my-2">Supplier Notes</label>
                                                <textarea id="replacementSupplierNotes" rows="2" placeholder="Notes"  name="supplier_notes" class="form-control"></textarea>
                                            </div>receivedItem
                                            <div class="col-12 col-md-2 mb-3">
                                                <label for="replacementNotes" class="my-2">Issue Notes</label>
                                                <textarea id="replacementNotes" name="issue_notes" rows="2" placeholder="Notes" class="form-control"></textarea>
                                            </div>
                                        </div>

                                        <div class="row justify-content-end" style="border-top: 1px solid #DDE1E3;">
                                            <div class="col-auto mt-2">
                                                <button type="button" class="btn btn-danger" onclick="closeTheTab(${product.id},'replace')">Cancel</button>
                                                <button type="button" class="btn btn-primary" onclick="saveIssue(${product.id},'replace')">Create Replacement Event</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div></td>   
                        </tr>
                        <tr id="return_row_${product.id}" class="d-none">
                           <td colspan="13">
                                 <div class="card pb-1">
                                <div class="card-header">
                                     <h6>Return for Refund Event</h6>
                                </div>
                                <div class="card-body">
                                    <form id="returnForm${product.id}">
                                        <div class="row">
                                            <div class="col-12 col-md-1 mb-3">
                                                <label for="returnRefundQty" class="my-2"># of Items</label>
                                                <input type="number" id="returnRefundQty" name="item_quantity" min="0" step="1" class="form-control">
                                            </div>
                                            <div class="col-12 col-md-1 mb-3">
                                                <label for="refundedItem${product.id}" class="my-2">Refunded?</label>
                                                <button type="button" class="btn btn-light">
                                                    <div class="form-check me-2">
                                                        <input class="form-check-input" type="checkbox" id="refundedItem${product.id}" name="refunded">
                                                        <label class="form-check-label" for="refundedItem${product.id}"><i aria-hidden="true" class="fa fa-square-o"></i> Yes</label>
                                                    </div>
                                                </button>
                                            </div>
                                            <div class="col-12 col-md-2 mb-3">
                                                <label for="expectedRefund" class="my-2">Expected Refund</label>
                                                <input type="number" id="expectedRefund" name="refund_expected" min="0.00" placeholder="Expected Amount" class="form-control">
                                            </div>
                                            <div class="col-12 col-md-2 mb-3">
                                                <label for="actualRefund" class="my-2">Actual Refund</label>
                                                <input type="number" id="actualRefund" name="refund_actual" min="0.00" placeholder="Actual Amount" class="form-control">
                                            </div>
                                            <div class="col-12 col-md-2 mb-3">
                                                <label for="refundTrackingNumber" class="my-2">Tracking Number</label>
                                                <input type="text" id="refundTrackingNumber" placeholder="Tracking #" class="form-control" name="tracking_number">
                                            </div>
                                            <div class="col-12 col-md-2 mb-3">
                                                <label for="returnRefundSupplierNotes" class="my-2">Supplier Notes</label>
                                                <textarea id="returnRefundSupplierNotes" rows="2" placeholder="Notes" name="supplier_notes" class="form-control"></textarea>
                                            </div>
                                            <div class="col-12 col-md-2 mb-3">
                                                <label for="issueNotes" class="my-2">Issue Notes</label>
                                                <textarea id="issueNotes" name="issue_notes" rows="2" placeholder="Notes" class="form-control"></textarea>
                                            </div>
                                        </div>

                                        <div class="row justify-content-end" style="border-top: 1px solid #DDE1E3;">
                                            <div class="col-auto mt-2">
                                                <button type="button" class="btn btn-danger" onclick="closeTheTab(${product.id},'return')">Cancel</button>
                                                <button type="button" class="btn btn-primary" onclick="saveIssue(${product.id},'return')">Create Return Event</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            </td>
                        </tr>
                        <tr id="never_received_row_${product.id}" class="d-none">
                            <td colspan="13">
                                <div class="card pb-1">
                                    <div class="card-header">
                                        <h6>Never Received Event</h6>
                                    </div>
                                    <div class="card-body">
                                        <form id="receivedFrom${product.id}">
                                            <div class="row">
                                                <div class="col-12 col-md-1 mb-3">
                                                    <label for="itemsNotRecieved" class="my-2"># of Items</label>
                                                    <input type="number" id="itemsNotRecieved" min="0" step="1" class="form-control" name="item_quantity">
                                                </div>
                                                <div class="col-12 col-md-1 mb-3">
                                                    <label for="orderItemCancelled${product.id}" class="my-2">Cancelled?</label>
                                                     <button type="button" class="btn btn-light">
                                                        <div class="form-check me-2">
                                                            <input class="form-check-input" type="checkbox" id="orderItemCancelled${product.id}" name="cancelled">
                                                            <label class="form-check-label" for="orderItemCancelled${product.id}"><i aria-hidden="true" class="fa fa-square-o"></i> Yes</label>
                                                        </div>
                                                    </button>
                                                </div>
                                                <div class="col-12 col-md-1 mb-3">
                                                    <label for="ccCharged${product.id}" class="my-2">CC charged?</label>
                                                    <button type="button" class="btn btn-light">
                                                        <div class="form-check me-2">
                                                            <input class="form-check-input" type="checkbox" id="ccCharged${product.id}" name="cc_charged">
                                                            <label class="form-check-label" for="ccCharged${product.id}"><i aria-hidden="true" class="fa fa-square-o"></i> Yes</label>
                                                        </div>
                                                    </button>
                                                </div>
                                                <div class="col-12 col-md-1 mb-3">
                                                    <label for="refundedItem${product.id}" class="my-2">Refunded?</label>
                                                     <button type="button" class="btn btn-light">
                                                        <div class="form-check me-2">
                                                            <input class="form-check-input" type="checkbox" id="refundedItem${product.id}" name="refunded">
                                                            <label class="form-check-label" for="refundedItem${product.id}" name="refunded"><i aria-hidden="true" class="fa fa-square-o"></i> Yes</label>
                                                        </div>
                                                    </button>
                                                </div>
                                                <div class="col-12 col-md-2 mb-3">
                                                    <label for="expectedRefund" class="my-2">Expected Refund</label>
                                                    <input type="number" id="expectedRefund" min="0.00" placeholder="Expected Amount" name="refund_expected" class="form-control">
                                                </div>
                                                <div class="col-12 col-md-2 mb-3">
                                                    <label for="actualRefund" class="my-2">Actual Refund</label>
                                                    <input type="number" id="actualRefund" name="refund_actual" min="0.00" placeholder="Actual Amount" class="form-control">
                                                </div>
                                                <div class="col-12 col-md-2 mb-3">
                                                    <label for="supplierNotes" class="my-2">Supplier Notes</label>
                                                    <textarea id="supplierNotes"  name="supplier_notes" rows="2" placeholder="Notes" class="form-control"></textarea>
                                                </div>
                                                <div class="col-12 col-md-2 mb-3">
                                                    <label for="issueNotes" class="my-2">Issue Notes</label>
                                                    <textarea id="issueNotes" rows="2" name="issue_notes" placeholder="Notes" class="form-control"></textarea>
                                                </div>
                                            </div>
                                            <div class="row justify-content-end" style="border-top: 1px solid #DDE1E3;">
                                                <div class="col-auto mt-2">
                                                    <button type="button" class="btn btn-danger" onclick="closeTheTab(${product.id},'received')">Cancel</button>
                                                    <button type="button" class="btn btn-primary" onclick="saveIssue(${product.id},'received')">Create Never Received Event</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>    
                        </tr>
                        <tr id="events_row${product.id}" class="d-none">
                            
                        </tr>
                        <tr id="shipping_event_row${product.id}" class="d-none">
                            
                        </tr>
                        `;
                        productTableBody.append(row);
                    });
                    // Update product count
                    productCount.text(`(${items.length})`);
                } else {
                    alert('Error loading items: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('An error occurred while loading the items.');
            }
        });
    }
    // Show input or textarea on double-click
    function showEditableInput(cell) {
        $(cell).find('.display-text').hide();
        $(cell).find('.edit-input').show().focus();
    }
    function showEditableTextarea(cell) {
        $(cell).find('.display-text').hide();
        $(cell).find('.edit-textarea').show().focus();
    }
    // Update value and display it
    function updateValue(inputElement, fieldName) {
        let newValue = inputElement.value.trim();

        // Parse and validate numeric fields
        if (fieldName === 'buy_cost' || fieldName === 'sku_total' || fieldName === 'list_price' || fieldName === 'min' || fieldName === 'max') {
            newValue = parseFloat(newValue);
            if (isNaN(newValue)) newValue = 0; // Default to 0 if not a valid number
            inputElement.previousElementSibling.textContent = `$${newValue.toFixed(2)}`;
        } else {
            // Handle text fields
            inputElement.previousElementSibling.textContent = newValue || 'Add Notes Here';
        }

        // Hide the input or textarea and show updated text
        $(inputElement).hide();
        $(inputElement).siblings('.display-text').show();
    }
    // Function to attach event listeners for editing and deleting
    function attachEventListeners() {
        // Update item when unit or cost changes
        $('.unit-input, .cost-input').on('change', function() {
            var parentRow = $(this).closest('.row');
            var index = parentRow.data('index');
            var unit_purchased = parentRow.find('.unit-input').val();
            var sku_total = parentRow.find('.cost-input').val();

            // Update the total price in the UI
            parentRow.find('.total-price').text(`$${(unit_purchased * sku_total).toFixed(2)}`);

            // Update the item in the items array
            items[index].unit_purchased = unit_purchased;
            items[index].sku_total = sku_total;

            // Send AJAX request to update the item in the database
            updateItemInDb(items[index]);
        });

        // Handle delete item
        $('.delete-item-btn').on('click', function() {
            var itemId = $(this).data('id');
            var parentRow = $(this).closest('.row');

            if (confirm("Are you sure you want to delete this item?")) {
                // Send AJAX request to delete the item from the database
                deleteItemFromDb(itemId, parentRow);
            }
        });
    }
    // Function to update the item in the database via AJAX
    function updateItemInDb(item) {
        $.ajax({
            url: '{{ url("update-order-item") }}', // Adjust this URL to your actual endpoint
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            data: {
                id: item.id,  // ID of the item being updated
                unit_purchased: item.unit_purchased,
                sku_total: item.sku_total
            },
            success: function(response) {
                if (response.success) {
                    console.log('Item updated successfully');
                } else {
                    alert('Error updating item: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('An error occurred while updating the item.');
            }
        });
    }
    // Function to delete the item from the database via AJAX
    function deleteItemFromDb(itemId, rowElement) {
        $.ajax({
            url: "{{ url('delete-order-item') }}",  // Adjust this URL to your actual endpoint
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            data: {
                id: itemId  // ID of the item to be deleted
            },
            success: function(response) {
                if (response.success) {
                    // Remove the item from the items array
                    items = items.filter(item => item.id !== itemId);

                    // Remove the row from the UI
                    rowElement.remove();
                } else {
                    alert('Error deleting item: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('An error occurred while deleting the item.');
            }
        });
    }
    // Function to delete an item
    window.deleteItem = function(index) {
        items.splice(index, 1); // Remove the item from the array
        displaySavedItems();    // Re-render the list
    };
    // Clear input fields if the close button is clicked
    window.clearInputs = function(button) {
        $(button).closest('.new-item-row').remove();
        $('#addNewOrderItemBtn').show();
    }
    $(document).ready(function () {
        // Initialize the textarea hidden by default
        fetchFiles();
        getAllEvents();
        // Listen for changes in any input, select, or textarea
        $('input, select, textarea').on('input change', function () {
            // Show the save button when a change is detected
            $('#saveButton').removeClass('d-none');
        });
        // Function to handle Edit action
    window.editItem = function (id) {
        $.ajax({
            url: `{{ url('/items/${id}/edit') }}`,
            type: 'GET', // Use GET to fetch the edit form or details
            success: function (data) {
                $('#editITemModal').modal('show');
                $('#editITemModal #editUpc').val(data.upc)
                $('#editITemModal #editMsku').val(data.msku)
                $('#editITemModal #editOrderNote').val(data.product_buyer_notes)
                $('#editITemModal #editMaxPrice').val(data.max)
                $('#editITemModal #editMinPrice').val(data.min)
                $('#editITemModal #editListPrice').val(data.min)
                $('#editITemModal #editQty').val(data.unit_purchased)
                $('#editITemModal #orderName').val(data.name)
                $('#editITemModal #orderAsin').val(data.asin)
                $('#editITemModal #orderCost').val(data.buy_cost)
                $('#editITemModal #orderCategory').val(data.category)
                $('#editITemModal #orderSellingPrice').val(data.selling_price)
                $('#editITemModal #orderSupplier').val(data.supplier)
                $('#editITemModal #orderPromo').val(data.promo)
                $('#editITemModal #coupon_code').val(data.coupon_code)
                $('#editITemModal #orderSourceUrl').val(data.url)
                $('#editITemModal #productNote').val(data.order_note)
                $('#editITemModal #orderBsr').val(data.bsr)
                $('#editITemModal #amazonUrl').attr('href','https://www.amazon.com/dp/'+data.asin+'')
                $('#editITemModal .source_url').attr('href',data.url)
                $('#editITemModal .source_url').attr('href',data.url)
                // $('#editITemModal').modal('show');
                if(data.is_disputed == 1){
                    $('#editITemModal #orderIsDisputed').prop('checked',true).trigger('change');
                }
                if(data.is_hazmat == 1){
                    $('#editITemModal #orderIsHazmat').prop('checked',true).trigger('change');
                }
            },
            error: function (xhr) {
            }
        });
    };
    // Function to handle Copy/Split Order Item action
    window.copyNSlpitItem = function (id) {
        $.ajax({
            url: `{{ url('/items/${id}/duplicate') }}`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            success: function (response) {
                toastr.success(response.message); 
                if(response.total_items){
                    $('#totalItemOrders').text(response.total_items);
                }
                // alert('Copy/Split action successful for Product ID: ' + id);
                loadOrderItems(orderId)
            },
            error: function (xhr) {
                toastr.error('Copy/Split action failed:', xhr); 
            }
        });
    };
    // Function to handle Copy Item to Buylist action
    window.copuItemBuylist = function (id) {
        $.ajax({
            url: `{{ url('copyto/${id}/buylist') }}`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            data: { id: id },
            success: function (response) {
                toastr.success(response.message); 
            },
            error: function (xhr) {
                toastr.success('Add to Buylist action failed:', xhr); 
            }
        });
    };
    // Function to handle Delete action
    window.deleteITem = function (id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This cannot be undone.  *Leads will be moved to the Team Buylist*",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Add your AJAX request here to delete the buylist
                $.ajax({
                    url: `{{ url('/items/${id}/delete') }}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    success: function (response) {
                        toastr.success(response.message); 
                        if(response.total_items){
                            $('#totalItemOrders').text(response.total_items);
                        }
                        loadOrderItems(orderId)
                    },
                    error: function (xhr) {
                        toastr.error('delete failed:', xhr); 
                    }
                });
               
            }
        }); 
    };
    // Function to handle Reject action
    window.rejectItem = function (id) {
        $.ajax({
            url: `{{ url('/items/${id}/reject') }}`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            success: function (response) {
                toastr.success(response.message); 
                if(response.total_items){
                    $('#totalItemOrders').text(response.total_items);
                }
                loadOrderItems(orderId)
            },
            error: function (xhr) {
                toastr.error('Rejected failed:', xhr); 
            }
        });
    };
    });
    // Function to create file card HTML
    function createFileCard(file) {
        // console.log(file)
        return `<div class="col-md-12">
            <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <!-- Left side: Image, Name, Note -->
                <div class="d-flex align-items-center">
                    <img src="https://media.istockphoto.com/id/1222357475/vector/image-preview-icon-picture-placeholder-for-website-or-ui-ux-design-vector-illustration.jpg?s=612x612&w=0&k=20&c=KuCo-dRBYV7nz2gbk4J9w1WtTAgpTdznHu55W9FjimE=" class="me-3" alt="Avatar" style="width: 50px; height: 50px;">
                    <div>
                    <h6 class="mb-0">${file.name}</h6>
                    <small class="text-muted">Note: ${file.note}</small>
                    </div>
                </div>

                <!-- Right side: Icons and Dropdown -->
                <div class="d-flex align-items-center">
                    <!-- Eye Icon with Tooltip for View -->
                    <button class="btn btn-light me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="View File" onclick="viewFile('${file.view_url}', '${file.name}')">
                    <i class="ri-eye-line"></i>
                    </button>
                    
                    <!-- Download Icon with Tooltip for Download -->
                    <button class="btn btn-light me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Download File" onclick="downloadFile('${file.view_url}','${file.name}')">
                    <i class="ri-download-cloud-2-fill"></i>
                    </button>

                    <!-- Three dots dropdown for Edit/Delete -->
                    <div class="dropdown">
                    <button class="btn btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="mdi mdi-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" onclick="editFile(${file.id}, '${file.name}', '${file.note}')">Edit</a></li>
                        <li><a class="dropdown-item" href="#" onclick="deleteFile(${file.id})">Delete</a></li>
                    </ul>
                </div>
            </div>
          </div>
        </div>
      </div>`;
    }
    // Function to open the modal with current file info for editing
    function editFile(fileId, fileName, fileNote) {
        $('#fileName').val(fileName);
        $('#fileNote').val(fileNote);
        $('#fileID').data('fileId', fileId); // Save fileId to modal for later use
        $('#editFileModal').modal('show');
    }
    // Function to save changes after editing
    function saveFileChanges() {
        const fileId = $('#fileID').data('fileId');
        const newFileName = $('#fileName').val();
        const newFileNote = $('#fileNote').val();
        
        $.ajax({
            url: "{{ url('/files/update') }}", // Update to your route for handling file updates
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                file_id: fileId,
                name: newFileName,
                note: newFileNote
            },
            success: function(response) {
                if (response.success) {
                    // Hide modal
                    fetchFiles()
                    toastr.success('Updated Successfully!'); 
                    $('#editFileModal').modal('hide');
                } else {
                    toastr.error('Error updating file. Please try again.!'); 
                }
            },
            error: function(xhr) {
                alert('An error occurred. Please try again.');
            }
        });
    }
    // Function to delete the file
    function deleteFile(fileId) {
    if (confirm('Are you sure you want to delete this file?')) {
        $.ajax({
            url: '{{ url("files/delete") }}', // Update to your route for handling file deletions
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                file_id: fileId
            },
            success: function(response) {
                if (response.success) {
                    fetchFiles()
                    // Remove file card from the DOM
                    toastr.success('Deleted Successfully!'); 
                } else {
                    toastr.success('Failed to delete file.!'); 
                }
            },
            error: function(xhr) {
                alert('An error occurred while trying to delete the file.');
            }
        });
    }
    }
    // Function to view the file in a modal
    function viewFile(filePath, fileName) {
        const domain = window.location.origin;
        // Prepend the domain to the file path
        const fullFilePath = domain+'/storage/' + filePath;
        // Set the full file path to the modal image element
        document.getElementById('filePreview').src = fullFilePath;
        // Optionally, set the file name in the modal title
        document.getElementById('viewFileModalLabel').textContent = fileName;
        // Show the modal
        var myModal = new bootstrap.Modal(document.getElementById('viewFileModal'));
        myModal.show();
    }

    // Function to download the file
    function downloadFile(filePath, fileName) {
        const domain = window.location.origin;
        // Prepend the domain to the file path
        const fullFilePath = domain + '/storage/' + filePath;
        var downloadUrl = fullFilePath;        
        var a = document.createElement('a');
        // Set the href attribute to the download URL
        a.href = downloadUrl;
        // Set the download attribute with a custom filename
        a.download = fileName; // Replace .ext with the file's actual extension if needed
        
        // Append the anchor to the body
        document.body.appendChild(a);
        // Programmatically click the link to trigger the download
        a.click();
        // Remove the anchor from the DOM after the click
        document.body.removeChild(a);
    }
    // Fetch and display files
    function fetchFiles() {
        $.ajax({
            url: "{{ url('/orderattachments/list') }}",  // Change this to your backend route
            data:{
                order_id:orderId
            },
            method: 'GET',
            success: function (response) {
                var fileCards = $('#fileCards');
                fileCards.empty();
                response.forEach(function (file) {
                    var card = createFileCard(file);
                    fileCards.append(card);
                });
            },
            error: function (xhr, status, error) {
                alert('Error fetching files: ' + error);
            }
        });
    }
    function saveChanges() {
        // Collect all form data into an object
        let formData = {};
        // Get values from all input, select, and textarea fields
        $('input, select, textarea').each(function () {
            let field = $(this);
            formData[field.attr('name')] = field.val(); // Use field name as key
        });
        // Check if order_id is null
        if (!formData['order_id']) { 
            toastr.error('Order ID is required'); // Display error toaster message
            return false; // Prevent further submission
        }
        formData['id'] = orderId;
        // Send the data via AJAX
        $.ajax({
            url: "{{ url('save-orders-updated') }}", // Replace with your actual endpoint
            type: 'POST',
            data: formData,
            success: function (response) {
                toastr.success("Data saved successfully!");
                // $('#saveButton').addClass('d-none'); // Hide the Save button after saving
            },
            error: function (error) {
                console.error("An error occurred:", error);
            }
        });
    }
    function changeBackgroundColor() {
        const select = document.getElementById("orderStatus");
        const selectedOption = select.value;

        // Reset styles
        select.style.backgroundColor = "";
        select.style.color = "";

        // Set a background color based on the selected option
        switch (selectedOption) {
            case "draft":
                select.style.backgroundColor = "#6c757d"; // Bootstrap secondary color
                break;
            case "ordered":
                select.style.backgroundColor = "#007bff"; // Bootstrap primary color
                break;
            case "partially received":
                select.style.backgroundColor = "#ffc107"; // Bootstrap warning color
                break;
            case "received in full":
                select.style.backgroundColor = "#28a745"; // Bootstrap success color
                break;
            case "reconcile":
                select.style.backgroundColor = "#17a2b8"; // Bootstrap info color
                break;
            case "closed":
                select.style.backgroundColor = "#343a40"; // Bootstrap dark color
                break;
            default:
                select.style.backgroundColor = ""; // Default color
                break;
        }

        // Optionally change text color
        if (selectedOption === "closed" || selectedOption === "draft") {
            select.style.color = "white"; // Light text for darker backgrounds
        } else {
            select.style.color = "black"; // Dark text for lighter backgrounds
        }
    }

    function toggleEditNote(){
        toggleNotesTextaera();
    }
    $('#noteText').on('dblclick',function(){
        $('#noteTextarea').removeClass('d-none'); // Remove the 'd-none' class
        $('#noteText').addClass('d-none');
    })
    $('.order-notes').on('dblclick',function(){
        $('#noteTextarea').removeClass('d-none'); // Remove the 'd-none' class
        $('#noteText').addClass('d-none');
    })
    function toggleNotesTextaera(){
        if ($('#noteTextarea').hasClass('d-none')) {
            $('#noteTextarea').removeClass('d-none'); // Remove the 'd-none' class
            $('#noteText').addClass('d-none');
        } else {
            $('#noteText').removeClass('d-none');
            $('#noteText').text($('#noteTextarea').val()??'')
            $('#noteTextarea').addClass('d-none'); // Add the 'd-none' class
        }
    }
    // Toggle the textarea size when clicking inside the card body
    $('#noteCard-body').on('click', function () {
        let $textarea = $('#noteTextarea');
        if ($textarea.hasClass('d-none')) {
            $textarea.removeClass('d-none');
        }
    });

    // Hide and reset the textarea when clicking outside
    $(document).on('click', function (event) {
        if (
            !$(event.target).closest('#noteCard-body').length &&
            !$(event.target).hasClass('edit-note')
        ) {
            $('#noteTextarea').addClass('d-none');
            $('#noteText').removeClass('d-none');
            $('#noteText').text($('#noteTextarea').val()??'')
            $('#noteTextarea').addClass('d-none'); // Add the 'd-none' class
        }
    });
    // Initialize with the current selection's background color
    window.onload = changeBackgroundColor;
    function addItem(){
        $('#bundleModal').modal('show');
    }
    $('#asin').on('input',function(){
        $('.amazonUrl').attr('href','https://www.amazon.com/dp/'+$(this).val()+'')
    })
    $('#url').on('input',function(){
        $('.source_url').attr('href',$(this).val())
    })
    $('#orderSourceUrl').on('input',function(){
        $('.source_url').attr('href',$(this).val())
    })
    $('#orderAsin').on('input',function(){
        $('#amazonUrl').attr('href','https://www.amazon.com/dp/'+$(this).val()+'')
    })
    $('.btn-number[data-type="plus"]').click(function(e) {
            e.preventDefault();
            var input = $(this).closest('.input-group').find('.input-number');
            var currentValue = parseInt(input.val());
            
            if (!isNaN(currentValue)) {
                input.val(currentValue + 1); // Increment the value
            } else {
                input.val(1); // Set to 1 if input is not a number
            }
        });
        // Handle the minus button click
        $('.btn-number[data-type="minus"]').click(function(e) {
            e.preventDefault();
            var input = $(this).closest('.input-group').find('.input-number');
            var currentValue = parseInt(input.val());

            if (!isNaN(currentValue) && currentValue > 1) {
                input.val(currentValue - 1); // Decrement the value
            } else {
                input.val(1); // Ensure value does not go below 1
            }
        });
        // Optional: Restrict direct input to only positive numbers
        $('.input-number').on('input', function() {
            var value = parseInt($(this).val());
            if (isNaN(value) || value < 1) {
                $(this).val(1); // Ensure value stays positive
            }
        });
         // When "Hazmat" checkbox is changed
        $('#orderIsHazmat').change(function() {
            if ($(this).is(':checked')) {
                // If checked, set the button color to red
                $(this).closest('button').removeClass('btn-light').addClass('btn-danger');
            } else {
                // If unchecked, reset the button color to light
                $(this).closest('button').removeClass('btn-danger').addClass('btn-light');
            }
        });

        // When "Disputed" checkbox is changed
        $('#orderIsDisputed').change(function() {
            if ($(this).is(':checked')) {
                // If checked, set the button color to yellow
                $(this).closest('button').removeClass('btn-light').addClass('btn-warning');
            } else {
                // If unchecked, reset the button color to light
                $(this).closest('button').removeClass('btn-warning').addClass('btn-light');
            }
        });
        $('.saveOrderBtn').on('click', function() {
            // Gather form data
            const orderData = {
                unit_purchased: $('#bundleModal #quantity').val(),
                order_id: orderId,
                list_price: $('#bundleModal #listPrice').val(),
                msku: $('#bundleModal #msku').val(),
                product_buyer_notes: $('#bundleModal #orderNote').val(),
                min: $('#bundleModal #minPrice').val(),
                max: $('#bundleModal #maxPrice').val(),
                name: $('#bundleModal #orderName').val(),
                asin: $('#bundleModal #orderAsin').val(),
                category: $('#bundleModal #orderCategory').val(),
                supplier: $('#bundleModal #orderSupplier').val(),
                source_url: $('#bundleModal #orderSourceUrl').val(),
                oorder_note: $('#bundleModal #orderProductNote').val(),
                buy_cost: $('#bundleModal #orderCost').val(),
                selling_price: $('#bundleModal #orderSellingPrice').val(),
                bsr: $('#bundleModal #orderBsr').val(),
                promo: $('#bundleModal #orderPromo').val(),
                coupon_code: $('#bundleModal #coupon_code').val(),
                isHazmat: $('#bundleModal #orderIsHazmat').is(':checked'),
                isDisputed: $('#bundleModal #orderIsDisputed').is(':checked')
            };

            // Send data via AJAX to your backend (assuming a URL like '/saveOrder')
            $.ajax({
                url: "{{ url('add-item-data') }}",
                type: 'POST',
                data: JSON.stringify(orderData),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                success: function(response) {
                    if(response.success){
                        toastr.success('item saved successfully!');
                        if(response.total_items){
                            $('#totalItemOrders').text(response.total_items);
                        }
                        loadOrderItems(orderId)
                        $('#bundleModal').modal('hide');
                    }else{
                        toastr.success('Order and item saved successfully!');
                    }
                },
                error: function(error) {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    function handleDropdownClick(id,type){
        switch (type) {
        case 'ship':
            $('#shipping_fba_row_'+id).removeClass('d-none');
            getShipings(id)
            break;

        case 'replace':
            $('#replacement_row_'+id).removeClass('d-none');
            break;
        case 'received':
            $('#never_received_row_'+id).removeClass('d-none');
            break;
        case 'return':
            $('#return_row_'+id).removeClass('d-none');
            break;

        default:
            console.log("Action not recognized");
            break;
        }
    }
    function closeTheTab(id,type){
        switch (type) {
        case 'ship':
            $('#shipping_fba_row_'+id).addClass('d-none');
            var $form = $(`#shippingForm${id}`);
            emptyFormData($form);
            break;

        case 'replace':
            $('#replacement_row_'+id).addClass('d-none');
            var $form = $(`#replaceForm${id}`);
            emptyFormData($form);
            break;
        case 'received':
            $('#never_received_row_'+id).addClass('d-none');
            var $form = $(`#receivedFrom${id}`);
            emptyFormData($form);
            break;
        case 'return':
            $('#return_row_'+id).addClass('d-none');
            var $form = $(`#returnForm${id}`);
            emptyFormData($form);
            break;

        default:
            console.log("Action not recognized");
            break;
        }
    }
    function emptyFormData($form){
        $form.find('input[type="text"], input[type="number"], input[type="date"], textarea').val('');
        $form.find('select').prop('selectedIndex', 0); // Reset dropdowns to the first option
        // Uncheck all checkboxes and radio buttons
        $form.find('input[type="checkbox"], input[type="radio"]').prop('checked', false);

        // Reset any custom inputs if necessary
        $form.find('.dropdown-toggle').text('Select an option'); // Example for dropdowns
    }
    function saveIssue(productId, formType) {
        let formData = {};
        let formElement;

        // Select the form based on the form type
        switch (formType) {
            case 'replace':
                formElement = document.querySelector(`#replaceForm${productId}`);
                break;
            case 'return':
                formElement = document.querySelector(`#returnForm${productId}`);
                break;
            case 'received':
                formElement = document.querySelector(`#receivedFrom${productId}`);
                break;
            default:
                console.error('Invalid form type');
                return;
        }
        // Collect form data
        if (formElement) {
            const formInputs = formElement.querySelectorAll('input, textarea, select');
            formInputs.forEach(input => {
                if (input.type === 'checkbox') {
                    formData[input.name] = input.checked; // Store the checkbox value
                } else {
                    formData[input.name] = input.value; // Store input or textarea values
                }
            });
        }
        // Append additional data
        formData['order_id'] = orderId; // Append the order ID
        formData['order_item_id'] = productId; // Append the product ID
        formData['issue_type'] = formType; // Append the type
        if (!formData['item_quantity'] && formType === 'replace') {
            return;
        }
        if (!formData['item_quantity'] && formType === 'return') {
            return;
        }
        if (!formData['item_quantity'] && formType === 'received') {
            return;
        }
        $.ajax({
            url: "{{ url('save-event-logs') }}",
            type:'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token
            },
            success: function(data){
                if(data.success){
                    toastr.success(data.message);
                    if(data.item_total_error){
                        $('#itemTotalError'+productId).text(data.item_total_error)
                        $('#orderTotalError').text(data.order_total_error)
                    } 
                    getAllEvents();
                    getItemEventLogs(data.order_item_id);
                }
                switch (formType) {
                    case 'ship':
                    $('#shipping_fba_row_'+productId).addClass('d-none');
                    var $form = $(`#shippingForm${productId}`);
                    emptyFormData($form);
                    break;
                    case 'replace':
                        $('#replacement_row_'+productId).addClass('d-none');
                        var $form = $(`#replaceForm${productId}`);
                        emptyFormData($form);
                        break;
                    case 'received':
                        $('#never_received_row_'+productId).addClass('d-none');
                        var $form = $(`#receivedFrom${productId}`);
                        emptyFormData($form);
                        break;
                    case 'return':
                        $('#return_row_'+productId).addClass('d-none');
                        var $form = $(`#returnForm${productId}`);
                        emptyFormData($form);
                        break;

                    default:
                    console.log("Action not recognized");
                    break;
                }
            }
        });    
    }
    function getAllEvents(){
        $.ajax({
            url: `{{ url('get-order/${orderId}/eventlogs') }}`,
            type:"GET",
            data:{
                type:'order'
            },
            success: function(data){
                // console.log(data);
                if (data.evetns.length > 0 || data.shippingEvent.length > 0) {
                    $('#eventCard').removeClass('d-none');
                } else {
                    $('#eventCard').addClass('d-none');
                }
                // Assuming `data` is an array of objects
                var tableBody = $('#eventsTable tbody'); // Target the `<tbody>` of the table
                tableBody.empty(); // Clear existing rows if needed
                data.evetns.forEach(item => {
                    // Format created_at and updated_at using Moment.js
                    var formattedCreatedAt = formatDateWithMoment(item.created_at);
                    var formattedUpdatedAt = formatDateWithMoment(item.updated_at);
                    // Create a new row with <td> cells
                    var row = `
                        <tr>
                            <td>${item.issue_type === 'ship' ? 'Ship to FBA' : 
                                item.issue_type === 'replace' ? 'Replacement' : 
                                item.issue_type === 'return' ? 'Return for Refund' : 
                                item.issue_type === 'received' ? 'Never Received' : 'Unknown Type'}
                            </td>
                            <td>${item.line_item.asin??''}</td>
                            <td>${item.item_quantity}</td>
                            <td>${formattedCreatedAt}</td>
                            <td>${formattedUpdatedAt}</td>
                            <td>
                                <button id="dropdownActions6421" class="btn btn-sm btn-outline-danger float-end" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="small-screen-hide">Actions &nbsp;</span>
                                    <i class="mdi mdi-dots-vertical" aria-hidden="true"></i>
                                </button>
                                <!-- Dropdown Menu -->
                                <ul class="dropdown-menu" aria-labelledby="dropdownActions6421">
                                    <!-- Add your dropdown items here -->
                                    <li><a class="dropdown-item text-info" href="javascript:openEditEventModal(${item.id});"><i class=" ri-folder-open-fill"></i> Edit Shipping Event</a></li>
                                <li><a class="dropdown-item text-danger" href="javascript:deleteEventLog(${item.id},${item.order_item_id})"><i class=" ri-delete-bin-fill"></i> Delete Shipping Event</a></li>
                                </ul>
                            </td>
                        </tr>
                    `;
                    // Append the row to the table body
                    tableBody.append(row);
                });
                data.shippingEvent.forEach(item => {
                    // Format created_at and updated_at using Moment.js
                    var formattedCreatedAt = formatDateWithMoment(item.created_at);
                    var formattedUpdatedAt = formatDateWithMoment(item.updated_at);

                    // Create a new row with <td> cells
                    var row = `
                        <tr>
                            <td>Ship to FBA
                            </td>
                            <td>${item.order_item.asin??''}</td>
                            <td>${item.items}</td>
                            <td>${formattedCreatedAt}</td>
                            <td>${formattedUpdatedAt}</td>
                            <td>
                                <button id="dropdownActions6421" class="btn btn-sm btn-outline-success float-end" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="small-screen-hide">Actions &nbsp;</span>
                                    <i class="mdi mdi-dots-vertical" aria-hidden="true"></i>
                                </button>
                                <!-- Dropdown Menu -->
                                <ul class="dropdown-menu" aria-labelledby="dropdownActions6421">
                                    <!-- Add your dropdown items here -->
                                    <li><a class="dropdown-item text-info" href="javascript:editShippingEvent(${item.id})"><i class=" ri-folder-open-fill"></i> Edit Shipping Event</a></li>
                                <li><a class="dropdown-item text-danger" href="javascript:deleteShippingEvent(${item.id},${item.order_item_id})"><i class=" ri-delete-bin-fill"></i> Delete Shipping Event</a></li>
                                </ul>
                            </td>
                        </tr>
                    `;
                    // Append the row to the table body
                    tableBody.append(row);
                });


            }
        })
    }
    // Function to format the date using Moment.js
    function formatDateWithMoment(dateStr) {
        return moment(dateStr).format('MMM Do, YYYY');
    }
    window.deleteEventLog = function (id,productId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, remove this shipping event!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Add your AJAX request here to delete the buylist
                $.ajax({
                    url: `{{ url('/eventlog/${id}/delete') }}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    success: function (response) {
                        toastr.success(response.message); 
                        getAllEvents();
                        if(response.item_total_error){
                        $('#itemTotalError'+productId).text(response.item_total_error)
                        $('#orderTotalError').text(response.order_total_error)
                    } 
                    },
                    error: function (xhr) {
                        toastr.error('delete failed:', xhr); 
                    }
                });
               
            }
        }); 
    };
    function getErrorLogs(productId){
        const iconElement = document.getElementById(`icon-${productId}`);
        const buttonElement = document.getElementById(`toggleBtn-${productId}`);
        if (iconElement.classList.contains('ri-eye-fill')) {
            // Change to hide icon
           
            $('#events_row'+productId).removeClass('d-none');
            $('#shipping_event_row'+productId).removeClass('d-none');
            getItemEventLogs(productId);
            iconElement.classList.remove('ri-eye-fill');
            iconElement.classList.add('ri-eye-off-fill');
            buttonElement.setAttribute('title', 'Hide');
        } else {
            // Change to show icon
            $('#events_row'+productId).addClass('d-none');
            $('#shipping_event_row'+productId).addClass('d-none');
            iconElement.classList.remove('ri-eye-off-fill');
            iconElement.classList.add('ri-eye-fill');
            buttonElement.setAttribute('title', 'View');
        }
    }
    function getItemEventLogs(productId){
               
            $.ajax({
            url: `{{ url('get-order/${productId}/eventlogs') }}`,
            type:"GET",
            data:{
                type:'order_item'
            },
            success: function(data){
                var row ='';
                var shippingRow ='';
                var eventHtmlShip ='';
                data.evetns.forEach(item => {
                    // Create a new row with <td> cells
                    var itemEvent = '';
                    var EventMetadata	 = '';
                    if(item.issue_type == 'replace'){
                        itemEvent = `Tracking Number:  ${item.tracking_number}`;
                    }else if(item.issue_type == 'return' || item.issue_type == 'received'){
                        itemEvent = `<div class="d-flex justify-content-between" width="100px;">
                        <span>Expected Refund: <br>$${item.refund_expected} </span>
                        <span>Actual Refund: <br>$${item.refund_actual} </span>
                        </div>`;
                    }
                    if(item.issue_type == 'replace'){
                        EventMetadata = `<button type="button" class="btn btn-light ${item.received ==1?'bg-success':'bg-danger'}">
                            <div class="form-check me-2 text-white">
                                <input class="form-check-input" type="checkbox" id="receivedItemErrorTable${item.id}" ${item.received ==1?'checked':''}>
                                <label class="form-check-label" for="receivedItemErrorTable${item.id}"><i aria-hidden="true" class="fa fa-square-o"></i> Received</label>
                            </div>
                        </button>`
                    }else if(item.issue_type == 'return'){
                        EventMetadata = `<button type="button" class="btn btn-light ${item.refunded ==1?'bg-success':'bg-danger'}">
                            <div class="form-check me-2 text-white">
                                <input class="form-check-input" type="checkbox" id="refundedItemErrorTable${item.id}" ${item.refunded ==1?'checked':''}>
                                <label class="form-check-label" for="refundedItemErrorTable${item.id}"><i aria-hidden="true" class="fa fa-square-o"></i> Refunded</label>
                            </div>
                        </button>`;
                    }else if(item.issue_type == 'received'){
                        EventMetadata = `<button type="button" class="btn btn-light ${item.cancelled ==1?'bg-success':'bg-danger'}">
                            <div class="form-check me-2 text-white">
                                <input class="form-check-input" type="checkbox" id="cancelledItemErrorTable${item.id}" ${item.cancelled ==1?'checked':''}>
                                <label class="form-check-label" for="cancelledItemErrorTable${item.id}"><i aria-hidden="true" class="fa fa-square-o"></i> Cancelled</label>
                            </div>
                        </button>
                        <button type="button" class="btn btn-light ${item.cc_charged ==1?'bg-success':'bg-danger'}">
                            <div class="form-check me-2 text-white">
                                <input class="form-check-input" type="checkbox" id="cc_chargedItemErrorTable${item.id}" ${item.cc_charged ==1?'checked':''}>
                                <label class="form-check-label" for="cc_chargedItemErrorTable${item.id}"><i aria-hidden="true" class="fa fa-square-o"></i> Cc Charged</label>
                            </div>
                        </button>
                        <button type="button" class="btn btn-light ${item.refunded ==1?'bg-success':'bg-danger'}">
                            <div class="form-check me-2 text-white">
                                <input class="form-check-input" type="checkbox" id="RefundeddItemErrorTable${item.id}" ${item.refunded ==1?'checked':''}>
                                <label class="form-check-label" for="RefundeddItemErrorTable${item.id}"><i aria-hidden="true" class="fa fa-square-o"></i>Refunded</label>
                            </div>
                        </button>
                        `;

                    }
                    row += `<tr>
                        <td>${item.issue_type === 'ship' ? 'Ship to FBA' : 
                            item.issue_type === 'replace' ? 'Replacement' : 
                            item.issue_type === 'return' ? 'Return for Refund' : 
                            item.issue_type === 'received' ? 'Never Received' : 'Unknown Type'}
                        </td>
                        <td>${item.item_quantity}</td>
                        <td>${EventMetadata}</td>
                        <td>${itemEvent}</td>
                        <td>${item.supplier_notes} </td>
                        <td>${item.issue_notes} </td>
                        <td>
                            <button id="dropdownActions6421" class="btn btn-sm btn-outline-danger float-end" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="small-screen-hide"></span>
                                <i class="mdi mdi-dots-vertical" aria-hidden="true"></i>
                            </button>
                            <!-- Dropdown Menu -->
                            <ul class="dropdown-menu" aria-labelledby="dropdownActions6421">
                                <!-- Add your dropdown items here -->
                                <li><a class="dropdown-item text-info" href="javascript:openEditEventModal(${item.id})"><i class=" ri-folder-open-fill"></i> Edit Shipping Event</a></li>
                            <li><a class="dropdown-item text-danger" href="javascript:deleteEventLog(${item.id},${item.order_item_id})"><i class=" ri-delete-bin-fill"></i> Delete Shipping Event</a></li>
                            </ul>

                        </td>
                    </tr>`;
                });
                if(data.evetns.length>0){
                    eventHtml = `<td colspan="13">
                        <div class="card-footer" style="border-top: 1px solid rgb(208, 208, 208);">
                            <div class="row mt-2">
                                <div class="col-md-12">
                                <h5>Error Events</h5>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                <!-- Table Header -->
                                <thead class="thead-dark  border-danger">
                                    <tr>
                                    <th>Type</th>
                                    <th># of Items</th>
                                    <th>Status</th>
                                    <th>Event Metadata</th>
                                    <th>Supplier Notes</th>
                                    <th>Issue Notes</th>
                                    <th>Actions</th>
                                    </tr>
                                </thead>
                                <!-- Table Body -->
                                <tbody>
                                    ${row}
                                </tbody>
                                </table>
                            </div>
                        </div>
                    </td>`
                    $('#events_row'+productId).empty();
                    $('#events_row'+productId).append(eventHtml);
                }else{
                    $('#events_row'+productId).empty();
                    $('#events_row'+productId).append(`<td colspan="13"></td>`);
                }
                console.log(data.shippingEvent);
                data.shippingEvent.forEach(item => {
                    var expire_date = formatDateWithMoment(item.expire_date);
                    batchUrl = `{{ url('shippingbatch/${item.shipping_batch}') }}`
                    var badge = '';
                    if (item.description_matches_flag === 1 || item.image_matches_flag === 1 || item.title_matches_flag === 1 ||item.upc_matches_flag === 1){
                        badge ='<span class="badge bg-success">Pass</span>'
                    }else{
                        badge ='<span class="badge bg-danger">Failed</span>'
                    }

                    // Create a new row with <td> cells
                        shippingRow += `<tr>
                            <td><a href="${batchUrl}" target="_blank" class="btn btn-sm btn-outline-success">${item.shippingbatch != null?item.shippingbatch.name:'-'} <i class=" ri-share-box-line"></i></a></td>
                            <td>${item.items}</td>
                            <td>${badge}</td>
                            <td>${expire_date}</td>
                            <td>${item.asin_override}</td>
                            <td>${item.product_name_override}</td>
                            <td>${item.condition}</td>
                            <td>${item.product_upc}</td>
                            <td>${item.msku_orderride}</td>
                            <td>${item.shipping_notes}</td>
                            <td>
                                <button id="dropdownActions6421" class="btn btn-sm btn-outline-danger float-end" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="small-screen-hide"></span>
                                <i class="mdi mdi-dots-vertical" aria-hidden="true"></i>
                                </button>
                                <!-- Dropdown Menu -->
                                <ul class="dropdown-menu" aria-labelledby="dropdownActions6421">
                                    <!-- Add your dropdown items here -->
                                    <li><a class="dropdown-item text-info" href="javascript:editShippingEvent(${item.id});"><i class=" ri-folder-open-fill"></i> Edit Shipping Event</a></li>
                                <li><a class="dropdown-item text-danger" href="javascript:deleteShippingEvent(${item.id},${item.order_item_id})"><i class=" ri-delete-bin-fill"></i> Delete Shipping Event</a></li>
                                </ul>
                            
                            </td>
                        </tr>`;
                    
                });
                if(data.shippingEvent.length>0){
                    eventHtmlShip = `<td colspan="13">
                        <div class="card-footer" style="border-top: 1px solid rgb(208, 208, 208);">
                            <div class="row mt-2">
                                <div class="col-md-12">
                                <h5>Ship to FBA Events</h5>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                <!-- Table Header -->
                                <thead class="thead-dark border-success">
                                    <tr>
                                        <th>Shipping Batch</th>
                                        <th>Qty Shipped</th>
                                        <th>QC</th>
                                        <th>Expiration</th>
                                        <th>ASIN Override</th>
                                        <th>Title Override</th>
                                        <th>Condition</th>
                                        <th>UPC</th>
                                        <th>MSKU</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <!-- Table Body -->
                                <tbody>
                                    ${shippingRow}
                                </tbody>
                                </table>
                            </div>
                        </div>
                    </td>`
                    console.log(eventHtmlShip);
                    $('#shipping_event_row'+productId).empty();
                    $('#shipping_event_row'+productId).append(eventHtmlShip);
                }else{
                    $('#shipping_event_row'+productId).empty();
                    $('#shipping_event_row'+productId).append(`<td colspan="13"></td>`);
                }
            }
        })

    }
    function showHiddenInputs(id){
        const additionalInputs = $('.additionalInputs'+id);
        const input = $('#asinOverride'+id).val();
        if (input.trim() !== '') {
            // Show the additional inputs and add 'done' class
            additionalInputs.removeClass('d-none');
            additionalInputs.addClass('done');
        } else {
            // Hide the additional inputs and remove 'done' class
            additionalInputs.addClass('d-none');
            additionalInputs.removeClass('done');
        }
    }
    function getShipings(id){
        $.ajax({
            url:"{{ url('get-shipping') }}",
            type:"GET",
            success:function(data){
                // Assuming `data` is an array of objects with `id` and `name` properties
                var $select = $('#shippingBatch' + id);
                $select.empty(); // Clear any existing options
                data.forEach(function(item, index) {
                    $select.append(`
                        <option value="${item.id}" ${index === 0 ? 'selected' : ''}>${item.name}</option>
                    `);
                });
            }
        })
    }
    function openShippingModal(id){
        $('#p_idd').val(id);
        $('#shippingBatchModal').modal('show');
    }
    function saveShipping(){
        var shipping_name = $('#shipping_name').val();
        var shipping_date = $('#shipping_date').val();
        var market_place = $('#market_place').val();
        var tracking_number = $('#tracking_number').val();
        var shipping_notes = $('#shipping_notes').val();
        $.ajax({
            url:"{{ url('save-shipping-batch') }}",
            type:"POST",
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            data:{
               date:shipping_date,
               name:shipping_name,
               market_place:market_place,
               tracking_number:tracking_number,
               notes:shipping_notes,
               status:'Open'
            },
            success:function(data){
                toastr.success('Record Added successfully'); 
                getShipings($('#p_idd').val());
                $('#shippingBatchModal').modal('hide');
                $('#p_idd').val('');
            }
        })
    }
    function checkAll(id){
        if($('#qcCheckAll'+id).is(":checked")){
            $('.qc-checkbox'+id).prop('checked',true);
            $('#dropdownQcSelect'+id).removeClass('btn-outline-primary').addClass('btn-success');
        }else{
            $('.qc-checkbox'+id).prop('checked',false);
            $('#dropdownQcSelect'+id).removeClass('btn-success').addClass('btn-outline-primary');
        }
    }
    function singleCheck(id, dataID) {
        // Check if the specific checkbox is checked
        const isChecked = $('#' + dataID + id).is(':checked');
        const productId = id; // Assuming 'id' is already your product ID
        const allChecked = $('.qc-checkbox' + productId).length === $('.qc-checkbox' + productId + ':checked').length;
        if (allChecked) {
            $('#qcCheckAll'+id).prop('checked',true);
            $('#dropdownQcSelect'+productId).removeClass('btn-outline-primary').addClass('btn-success');
        } else {
            $('#qcCheckAll'+id).prop('checked',false);
            $('#dropdownQcSelect'+productId).removeClass('btn-success').addClass('btn-outline-primary');
        }
    }
    function saveShippingevent(id,type){
         // Collect form data
        let formData = {};
        let formElement;
        formElement = document.querySelector(`#shippingForm${id}`);
        if (formElement) {
            const formInputs = formElement.querySelectorAll('input, textarea, select');
            formInputs.forEach(input => {
                if (input.type === 'checkbox') {
                    formData[input.name] = input.checked; // Store the checkbox value
                } else {
                    formData[input.name] = input.value; // Store input or textarea values
                }
            });
        }
        formData['order_id'] = orderId;
        formData['order_item_id'] = id;
       // Serialize form data, including QC checkboxes
        $.ajax({
            url:"{{ url('sav-shipping-event') }}",
            type:"POST",
            data:formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            success:function(data){
                toastr.success(data.message);
                $('#shipping_fba_row_'+id).addClass('d-none');
                var $form = $(`#shippingForm${id}`);
                emptyFormData($form);
                $('#itemTotalReceived'+id).text(data.total_received_items)
                $('#itemTotalShipped'+id).text(data.total_ship_items)
                $('#ordertotalReceiced').text(data.total_received_order)
                $('#orderTotalShipped').text(data.total_ship_order)
                getAllEvents()
                getItemEventLogs(data.order_item_id)
            }
            
        })

    }
    function checkthisinput(id){
        if($('#itemsShipped'+id).val() > 0){
            $('#saveShippingBtn'+id).attr('disabled',false);
        }else{
            $('#saveShippingBtn'+id).attr('disabled',true);
        }
    }
    window.deleteShippingEvent = function (id,order_id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, remove this shipping event!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Add your AJAX request here to delete the buylist
                $.ajax({
                    url: `{{ url('/shipping/event/${id}/delete') }}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    success: function (data) {
                        toastr.success(data.message); 
                        getAllEvents();
                        $('#itemTotalReceived'+id).text(data.total_received_items)
                        $('#itemTotalShipped'+id).text(data.total_ship_items)
                        $('#ordertotalReceiced').text(data.total_received_order)
                        $('#orderTotalShipped').text(data.total_ship_order)
                    },
                    error: function (xhr) {
                        toastr.error('delete failed:', xhr); 
                    }
                });
               
            }
        }); 
    };
    function editShippingEvent(id){
        $('#editShippingItemModal').modal('show');
        $.ajax({
            url:`{{ url('get/event/${id}') }}`,
            type:"GET",
            data:{
                type:'ship'
            },
            success:function(data){
                $('#editShippingItemModal #ship_event_id').val(data.id);
                $('#editShippingItemModal #itemsToShip').val(data.items);
                $('#editShippingItemModal #asinOverride').val(data.asin_override);
                $('#editShippingItemModal #expirationDate').val(data.expire_date);
                $('#editShippingItemModal #titleOverride').val(data.product_name_override);
                $('#editShippingItemModal #condition').val(data.condition);
                $('#editShippingItemModal #mskuOverride').val(data.msku_orderride);
                $('#editShippingItemModal #listPrice').val(data.list_price_orverride);
                $('#editShippingItemModal #minOverride').val(data.min_orverride);
                $('#editShippingItemModal #maxOverride').val(data.max_orverride);
                $('#editShippingItemModal #upcOverride').val(data.product_upc);
                $('#editShippingItemModal #shippingNotes').val(data.shipping_notes);
                data.description_matches_flag == 1?$('#editShippingItemModal #descriptionMatchesFlag').prop('checked',true):$('#editShippingItemModal #descriptionMatchesFlag').prop('checked',false); 
                data.title_matches_flag == 1?$('#editShippingItemModal #titleMatchesFlag').prop('checked',true):$('#editShippingItemModal #titleMatchesFlag').prop('checked',false);
                data.upc_matches_flag == 1?$('#editShippingItemModal #upcMatchesFlag').prop('checked',true):$('#upcMatchesFlag').prop('chec ked',false);
                data.image_matches_flag == 1?$('#editShippingItemModal #imageMatchesFlag').prop('checked',true):$('#editShippingItemModal #imageMatchesFlag').prop('checked',false);
            }
        })
    }
    function updateShipEvent(){
         // Get all the form data
        var formData = {
            ship_event_id: $('#editShippingItemModal #ship_event_id').val(),
            // shippingBatch: $('#editShippingItemModal #shippingBatch').val(),
            items: $('#editShippingItemModal #itemsToShip').val(),
            upc_matches_flag: $('#editShippingItemModal #upcMatchesFlag').is(':checked') ? 1 : 0,
            title_matches_flag: $('#editShippingItemModal #titleMatchesFlag').is(':checked') ? 1 : 0,
            image_matches_flag: $('#editShippingItemModal #imageMatchesFlag').is(':checked') ? 1 : 0,
            description_matches_flag: $('#editShippingItemModal #descriptionMatchesFlag').is(':checked') ? 1 : 0,
            expire_date: $('#editShippingItemModal #expirationDate').val(),
            asin_override: $('#editShippingItemModal #asinOverride').val(),
            product_name_override: $('#editShippingItemModal #titleOverride').val(),
            condition: $('#editShippingItemModal #condition').val(),
            msku_orderride: $('#editShippingItemModal #mskuOverride').val(),
            list_price_orverride: $('#editShippingItemModal #listPrice').val(),
            min_orverride: $('#editShippingItemModal #minOverride').val(),
            max_orverride: $('#editShippingItemModal #maxOverride').val(),
            product_upc: $('#editShippingItemModal #upcOverride').val(),
            shipping_notes: $('#editShippingItemModal #shippingNotes').val()
        };
        // Send an AJAX request to update the shipping event
        $.ajax({
            url: `{{ url('/update-shipping-event') }}`,  // Your backend URL to handle the update
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            data: formData,
            success: function(response) {
                // Handle the response
                if(response.success) {
                    // Optionally, show a success message
                    alert('Shipping event updated successfully!');
                    // Close the modal
                    $('#editShippingItemModal').modal('hide');
                    getAllEvents()
                    getItemEventLogs(response.order_item_id)
                } else {
                    // Handle the error
                    alert('Error updating the shipping event: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                // Handle any error that occurred during the AJAX request
                console.error(error);
                alert('An error occurred while updating the shipping event.');
            }
        });
    }
    function openEditEventModal(id){
        $.ajax({
            url:`{{ url('get/event/${id}') }}`,
            type:"GET",
            data:{
                type:'other'
            },
            success:function(data){
                console.log(data);
                updateModalContent(data.issue_type,data);
                $('#EditeventModal').modal('show');
            }
        })

    }
    function updateModalContent(eventType,data) {
        const $conditionalInputs = $("#dyamic_data");
        const $modalLabel = $("#EditeventModalLabel");
        $conditionalInputs.empty();
        let inputHTML = "";
        $('#editevnetID').val('');
        $('#editevnetID').val(data.id);
        $('#eveNEditType').val('');
        $('#eveNEditType').val(eventType);
        if (eventType === "return") {
            inputHTML = `<h6>Return for Refund</h6>
            <form id="return_from${data.id}">
                <div class="row">
                    <!-- Number of Items -->
                    <div class="col-md-6 mb-3">
                        <label for="itemQtyEditIssue" class="my-2"># of Items</label>
                        <input type="number" name="item_quantity" id="itemQtyEditIssue" value="${data.item_quantity}" min="0" step="1" class="form-control">
                    </div>

                    <!-- Refunded? -->
                    <div class="col-md-6 text-center mb-3">
                        <div>
                            <label for="issueItemRefunded" class="my-2">Refunded?</label>
                        </div>
                        <div>
                        <button type="button" class="btn btn-light ${data.refunded ==1?'bg-success':'bg-danger'}">
                            <div class="form-check me-2 text-white">
                                <input class="form-check-input" name="refunded" type="checkbox" id="issueItemRefundedEdit${data.id}" ${data.refunded ==1?'checked':''}>
                                <label class="form-check-label" for="issueItemRefundedEdit${data.id}"><i aria-hidden="true" class="fa fa-square-o"></i>Yes</label>
                            </div>
                        </button>
                        </div>
                    </div>
                </div>

                <!-- Refund Details -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="issueItemRefundExpected" class="my-2">Expected Refund</label>
                        <input type="number" name="refund_expected" id="issueItemRefundExpected" value="${data.refund_expected}"  class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="issueItemRefundActual" class="my-2">Actual Refund</label>
                        <input type="number" id="issueItemRefundActual" name="refund_actual" value="${data.refund_actual}" class="form-control">
                    </div>
                </div>

                <!-- Tracking Number -->
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="issueItemTracking" class="my-2">Tracking Number</label>
                        <input type="text" id="issueItemTracking"  name="tracking_number" value="${data.tracking_number}" class="form-control">
                    </div>
                </div>

                <!-- Notes -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="issueItemSupplierNotes" class="my-2">Supplier Notes</label>
                        <textarea id="issueItemSupplierNotes" name="supplier_notes" rows="2" placeholder="Notes" class="form-control">${data.supplier_notes}</textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="issueItemIssueNotes" class="my-2">Issue Notes</label>
                        <textarea id="issueItemIssueNotes"name="issue_notes" rows="2" placeholder="Notes" class="form-control">${data.issue_notes}</textarea>
                    </div>
                </div>
            </form>`;
        } else if (eventType === "replace") {
            inputHTML = `<h6>Replacement</h6>
            <form id="replace_form${data.id}">
                <!-- Number of Items -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="itemQtyEditIssue" class="my-2"># of Items</label>
                        <input type="number" id="itemQtyEditIssue" name="item_quantity" value="${data.item_quantity}" min="0" step="1" class="form-control">
                    </div>
                    <div class="col-md-6 text-center">
                        <label for="issueItemReceived" class="my-2">Received?</label>
                        <button type="button" class="btn btn-light ${data.received ==1?'bg-success':'bg-danger'}">
                            <div class="form-check me-2 text-white">
                                <input name="received" class="form-check-input" type="checkbox" id="issueItemReceivedEdit${data.id}" ${data.received ==1?'checked':''}>
                                <label class="form-check-label" for="issueItemReceivedEdit${data.id}"><i aria-hidden="true" class="fa fa-square-o"></i> Yes</label>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Tracking Number -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="issueItemTracking" class="my-2">Tracking Number</label>
                        <input type="text" name="tracking_number" id="issueItemTracking" value="${data.tracking_number}" class="form-control">
                    </div>
                </div>

                <!-- Notes -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="issueItemSupplierNotes" class="my-2">Supplier Notes</label>
                        <textarea id="issueItemSupplierNotes" name="supplier_notes" rows="2" placeholder="Notes" class="form-control">${data.supplier_notes}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="issueItemIssueNotes" class="my-2">Issue Notes</label>
                        <textarea id="issueItemIssueNotes" name="issue_notes" rows="2" placeholder="Notes" class="form-control">${data.issue_notes}</textarea>
                    </div>
                </div>
            </form>`;
        } else if (eventType === "received") {
            inputHTML = `<h6>Never Received</h6>
            <form id="never_received_from${data.id}">
                <!-- Number of Items -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="itemQtyEditIssue" class="my-2"># of Items</label>
                        <input type="number" name="item_quantity" id="itemQtyEditIssue" value="${data.item_quantity}" min="0" step="1" class="form-control">
                    </div>

                    <div class="col-md-6 text-center">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="issueItemCancelled" class="my-2">Cancelled?</label>
                            </div>
                            <div class="col-md-4">
                                <label for="issueItemCharged" class="my-2">CC Charged?</label>
                            </div>
                            <div class="col-md-4">
                                <label for="issueItemRefunded" class="my-2">Refunded?</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <button type="button" class="btn btn-light ${data.cancelled ==1?'bg-success':'bg-danger'}">
                                    <div class="form-check me-2 text-white">
                                        <input name="cancelled"  class="form-check-input" type="checkbox" id="issueItemCancelledEdit${data.id}" ${data.cancelled ==1?'checked':''}>
                                        <label class="form-check-label" for="issueItemCancelledEdit${data.id}"><i aria-hidden="true" class="fa fa-square-o"></i> Yes</label>
                                    </div>
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-light ${data.cc_charged ==1?'bg-success':'bg-danger'}">
                                    <div class="form-check me-2 text-white">
                                        <input name="cc_charged"  class="form-check-input" type="checkbox" id="issueItemCCChargedEdit${data.id}" ${data.cc_charged ==1?'checked':''}>
                                        <label class="form-check-label" for="issueItemCCChargedEdit${data.id}"><i aria-hidden="true" class="fa fa-square-o"></i> Yes</label>
                                    </div>
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-light ${data.refunded ==1?'bg-success':'bg-danger'}">
                                    <div class="form-check me-2 text-white">
                                        <input name="refunded" class="form-check-input" type="checkbox" id="issueItemRefundedEdit${data.id}" ${data.refunded ==1?'checked':''}>
                                        <label class="form-check-label" for="issueItemRefundedEdit${data.id}"><i aria-hidden="true" class="fa fa-square-o"></i> Yes</label>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Refund Details -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="issueItemRefundExpected" class="my-2">Expected Refund</label>
                        <input type="number" nanme="refund_expected" id="issueItemRefundExpected" value="${data.refund_expected}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="issueItemRefundActual" class="my-2">Actual Refund</label>
                        <input type="number" name="refund_actual" id="issueItemRefundActual"  value="${data.refund_actual}" class="form-control">
                    </div>
                </div>

                <!-- Notes -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="issueItemSupplierNotes" class="my-2">Supplier Notes</label>
                        <textarea id="issueItemSupplierNotes" name="supplier_notes" rows="2" placeholder="Notes" class="form-control">${data.supplier_notes}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="issueItemIssueNotes" class="my-2">Issue Notes</label>
                        <textarea id="issueItemIssueNotes" name="issue_notes" rows="2" placeholder="Notes" class="form-control">${data.issue_notes}</textarea>
                    </div>
                </div>
            </form>`;
        }
        $conditionalInputs.append(inputHTML);
    }
    function updateIssueEnvet(){
        var eventType = $('#eveNEditType').val();
        var id =$('#editevnetID').val();
        switch (eventType) {
            case 'return':
                var form = $('#return_from' + id);
                formData = form.serialize(); // Serialize the form data
                break;
            case 'replace':
                var form = $('#replace_form' + id);
                formData = form.serialize(); // Serialize the form data
                break;
            case 'received':
                var form = $('#never_received_from' + id);
                formData = form.serialize(); // Serialize the form data
                break;
            default:
                break;
        }
         // Send the form data via AJAX
        $.ajax({
            url: `{{ url('update-issue-event/${id}') }}`, // Your server endpoint to handle the update
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            data: formData, // Send the serialized form data
            success: function(response) {
                // Handle the response after successful submission
                toastr.success('Event updated successfully');
                if(data.item_total_error){
                    $('#itemTotalError'+id).text(data.item_total_error)
                    $('#orderTotalError').text(data.order_total_error)
                } 
                getAllEvents()
                getItemEventLogs(response.order_item_id)
                $('#EditeventModal').modal('hide');
                // Optionally, update the UI or reset the form
            },
            error: function(xhr, status, error) {
                // Handle errors
                toastr.error('An error occurred: ' + error);
            }
        });
    }
        // Calculate 125 days from today
        const today = new Date();
        const minAllowedDate = new Date(today.setDate(today.getDate() + 125));
        const formattedMinAllowedDate = minAllowedDate.toISOString().split('T')[0];
        function checkDate(id){
            const selectedDate = new Date($('#expirationDate'+id).val());
            const selectedDateFormatted = $('#expirationDate'+id).val();

            // Check if selected date is at least 125 days in the future
            if (selectedDate < minAllowedDate) {
                alert(
                    `Warning: The Expiration Date should be at least 125 days in the future. ` +
                    `Amazon will not accept most items that expire within 105 days of receipt. ` +
                    `If you were to ship today, this would allow 20 days travel time. ` +
                    `Please select a date no earlier than ${formattedMinAllowedDate}.`
                );
                $(this).val(''); // Clear invalid input
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            const displayText = document.getElementById('cash_back_percentage_display');
            const inputField = document.getElementById('cash_back_percentage_input');

            // When the display text is clicked
            displayText.addEventListener('click', () => {
            // Hide the text and show the input field
            displayText.classList.add('d-none');
            inputField.classList.remove('d-none');
            inputField.focus(); // Focus on the input for immediate editing
        });

        // When the input field loses focus
        inputField.addEventListener('blur', () => {
            // Update the display text with the input value
            const updatedValue = inputField.value ? parseFloat(inputField.value).toFixed(2) + '%' : '0.00%';
            displayText.textContent = updatedValue;
            // Hide the input field and show the text
            inputField.classList.add('d-none');
            displayText.classList.remove('d-none');
        });
});
document.addEventListener('DOMContentLoaded', () => {
    const salesTaxRateSpan = document.getElementById('sales_tax_rate');
    const salesTaxRateInput = document.getElementById('orderViewSalesTaxRate');

    // When the span is clicked, show the input field and hide the span
    salesTaxRateSpan.addEventListener('click', () => {
        salesTaxRateSpan.classList.add('d-none'); // Hide the span
        salesTaxRateInput.classList.remove('d-none'); // Show the input
        salesTaxRateInput.focus(); // Focus on the input field
    });

    // When the input loses focus, update the span and toggle back
    salesTaxRateInput.addEventListener('blur', () => {
        const inputValue = parseFloat(salesTaxRateInput.value) || 0; // Default to 0 if invalid
        salesTaxRateSpan.textContent = `${inputValue.toFixed(2)}%`; // Update span content with formatted value

        salesTaxRateInput.classList.add('d-none'); // Hide the input
        salesTaxRateSpan.classList.remove('d-none'); // Show the span
    });

    // Optional: Update span when pressing "Enter" in the input field
    salesTaxRateInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            salesTaxRateInput.blur(); // Trigger blur event to save value
        }
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const toggleIcon = document.getElementById('toggleCardBody');
    const cardBody = document.querySelector('.card-body');

    // Add click event to the eye icon
    toggleIcon.addEventListener('click', () => {
        if (cardBody.style.display === 'none') {
            cardBody.style.display = 'block'; // Show the card body
            toggleIcon.classList.remove('ri-eye-fill'); // Remove 'fa-eye' class
            toggleIcon.classList.add('ri-eye-off-fill'); // Add 'fa-eye-slash' class
        } else {
            cardBody.style.display = 'none'; // Hide the card body
            toggleIcon.classList.remove('ri-eye-off-fill'); // Remove 'fa-eye-slash' class
            toggleIcon.classList.add('ri-eye-fill'); // Add 'fa-eye' class
        }
    });
});

function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text)
            .then(() => {
                alert(`Copied: ${text}`);
            })
            .catch(err => {
                console.error('Failed to copy using clipboard API:', err);
            });
    } else {
        // Fallback for older browsers
        const input = document.createElement('input');
        input.value = text;
        document.body.appendChild(input);
        input.select();
        input.setSelectionRange(0, 99999); // For mobile devices
        try {
            document.execCommand('copy');
            toastr.success(`Copied: ${text}`); 
        } catch (err) {
            console.error('Failed to copy using execCommand:', err);
        }
        document.body.removeChild(input);
    }
}
$('#addCashbackSourceForm').on('submit', function (e) {
        e.preventDefault();
        let cashbackSourceName = $('#cashback-source-name').val();
        $.ajax({
            url: `{{url('add-cashback-source') }}`, // Update with your endpoint
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            data: { name: cashbackSourceName },
            success: function (response) {
                // Close the modal
                $('#addCashbackSourceModal').modal('hide');
                // Reset the form
                $('#addCashbackSourceForm')[0].reset();
               
                // Append new option to the select box
                $('#cash_back_source').append(
                    `<option value="${response.data.name}" selected>${response.data.name}</option>`
                );
                // Optionally, set the newly added option as selected
                $('#cash_back_source').val(response.data.name);
            },
            error: function () {
                alert('Failed to add cashback source.');
            }
        });
    });
$('#addEmailForm').on('submit', function(e) {
    e.preventDefault();
    // const formData = $(this).serialize();
    var email = $('#new-email').val();
    $.ajax({
        url: "{{ route('emails.store') }}",
        method: "post",
        data: {
            email:email
        },
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        success: function(response) {
            if (response.success) {
                $('#addEmailModal').modal('hide');
                $('#addEmailForm')[0].reset();
                // Append new option to the select box
                $('#email').append(
                    `<option value="${response.email.email}" selected>${response.email.email}</option>`
                );
                // Optionally, set the newly added option as selected
                $('#email').val(response.email.email);
                toastr.success(response.message || 'Email saved successfully!');
            } else {
                toastr.error(response.message || 'An error occurred.');
            }
        },
        error: function() {
            toastr.error('An error occurred. Please try again.');
        }
    });
});
function getDomain(url) {
    let domain = url.replace(/^(https?:\/\/)?(www\.)?/i, '');
    domain = domain.split('/')[0];
    return domain;
}
function popWorkOrders(id){
    $('#workOrderModal').modal('show');
    $('#lineItemId').val(id);
    $.ajax({
        url: 'http://app.prepcenter.me/api/get-prep-orders',
        type: 'GET',
        async:false,
        success: function(data) {
            // Assume `data` is an array of work orders like:
            // [{ id: 1, title: 'Prep Order 1' }, { id: 2, title: 'Prep Order 2' }]
            $('#workOrderSelect').empty();
            $.each(data,function(index,value){
                let employeeName = value.employee ? ` | ${value.employee.name}` : 'Unassigned';
                $('#workOrderSelect').append(
                    `<option value="${value.custom_id}">${value.custom_id} ${employeeName}</option>`
                );
            })  
        },
        error: function(err) {
            // handle error
        }
    });

}
$('#assignWorkOrderBtn').on('click',function(){
    var prepOrderId = $('#workOrderSelect').val();
    var lineItemId = $('#lineItemId').val();
     var productQtyInput = $('#productQtyInput').val();
    $.ajax({
        url: "{{ url('assign-work-order') }}",
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        data:{
            prepOrderId:prepOrderId,
            lineItemId:lineItemId,
            productQtyInput:productQtyInput,
        },
        success:function(data){
            alert(data.message);
            $('#workOrderModal').modal('hide');
        }
    })
})



</script>  
@endsection