<!-- Dropzone.js CSS CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css" integrity="sha512-aXjtrYtbLgHRzkP2yNUOeeM3JidDPM4pewQhZJZpEppT9dFMjWEYhz2hE7R2RD1fdf2fVByjDcfTe+hWiWaz+g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- jQuery CDN (optional, if you need jQuery for other parts) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Dropzone.js JS CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js" integrity="sha512-v2EEHXClYWUqDgPTBQaNEksVffxC5aRyIR+09tHbyyBDReNhiwDQ6V2kq0vdVZb9kQm1uUyrGmxS6KoVNPdvg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    var orderId = $('#order_id').val();
    var items = [];
    var grandTotalAll = 0;
    $('#pre_tax_discount, #post_tax_discount, #shipping_cost, #sales_tax, #is_sale_tax').on('input change', calculateTotals);
    // Initial calculation
    loadOrderItems(orderId)
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
    // Save item to the array and send to the server via AJAX
    $(document).on('click', '.save-item-btn', function() {
        var parentRow = $(this).closest('.new-item-row');
        var asin = parentRow.find('input[name="asin"]').val();
        var name = parentRow.find('input[name="name"]').val();
        var unit_purchased = parseFloat(parentRow.find('input[name="units"]').val()) || 0;
        var orderId = $('#order_id').val(); // Assuming you have an order_id field
        var calcType = $('#buy_cost_type').val(); // Get selected calculator type
        var buyCost = parseFloat(parentRow.find('input[name="buy_cost"]').val()) || 0;
        var skuTotal = parseFloat(parentRow.find('input[name="sku_total"]').val()) || 0;

        // Calculate buy_cost or sku_total based on selected type
        if (calcType === 'individual') {
            skuTotal = (buyCost * unit_purchased).toFixed(2); // Calculate total if type is 'individual'
        } else if (calcType === 'sku') {
            buyCost = (skuTotal / unit_purchased).toFixed(2); // Calculate per unit cost if type is 'sku'
        }

        // Validate inputs
        if (asin && name && unit_purchased > 0) {
            // Prepare item data with calculated cost
            var itemData = {
                asin: asin,
                name: name,
                unit_purchased: unit_purchased,
                buy_cost: buyCost,
                sku_total: skuTotal,
                order_id: orderId
            };

                // Send data via AJAX
            $.ajax({
                url: "{{ url('save-order-item') }}",  // Replace with your actual URL
                method: 'POST',
                data: itemData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                success: function(response) {
                    // Check response for success
                    if (response.success) {
                        items = response.items;
                        parentRow.remove();
                        $('#addNewOrderItemBtn').show();
                        loadOrderItems(orderId);
                        setTimeout(() => {
                            calculateTotals();
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
            alert("Please fill out all fields correctly.");
        }
    });

    $(document).on('input', '.unit-input, .total-cost-input, .cost-input', function() {
        var $input = $(this);
        var parentRow = $(this).closest('tr');
        var calcType = $('#buy_cost_type').val();
        var units = parseFloat(parentRow.find('.unit-input').val()) || 0;
        var buyCost = parseFloat(parentRow.find('.cost-input').val()) || 0;
        var skuTotal = parseFloat(parentRow.find('.total-cost-input').val()) || 0;
        var itemId = $input.data('id');  // Assuming each row has a unique identifier for the line item


        if (units > 0) {
            if (calcType === 'individual') {
                // If the calculator type is "individual", calculate the total cost based on per-unit cost
                skuTotal = (buyCost * units).toFixed(2);
                parentRow.find('.total-cost-input').val(skuTotal);
            } else if (calcType === 'sku') {
                // If the calculator type is "sku", calculate the per-unit cost based on total cost
                buyCost = (skuTotal / units).toFixed(2);
                parentRow.find('.cost-input').val(buyCost);
            }

            // Update calculated-cost cell only with the per-unit buyCost
            parentRow.find('.calculated-cost').text(`$${parseFloat(buyCost).toFixed(2)}`);
            // AJAX call to update the values in the database
            $.ajax({
                url: "{{ url('update-order-item') }}",  // Update this with the correct endpoint URL
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                data: {
                    id: itemId,
                    unit_purchased: units,
                    sku_total: skuTotal,
                    buy_cost: buyCost,
                },
                success: function(response) {
                    console.log('Database updated successfully:', response);
                },
                error: function(xhr, status, error) {
                    console.error('Database update failed:', error);
                }
            });
        }
    });
    var grandTotal = 0;
    var totalSalesTaxTotal = 0;
    var subtotal = 0;
    var totalQty = 0;
    function calculateTotals() {
        var preTaxDiscount = parseFloat($('#pre_tax_discount').val()) || 0;
        var postTaxDiscount = parseFloat($('#post_tax_discount').val()) || 0;
        var shippingCost = parseFloat($('#shipping_cost').val()) || 0;
        var salesTaxRate = parseFloat($('#sales_tax').val()) || 0;
        var isSalesTaxOnShipping = $('#is_sale_tax').is(':checked')?1:0;
        // Calculate total for all line items
        var lineItemsTotal = 0;
            totalQty = 0;
        $('#savedItemsContainer tr').each(function() {
            var units = parseFloat($(this).find('.unit-input').val()) || 0;
            var buyCost = parseFloat($(this).find('.cost-input').val()) || 0;
            var itemTotal = units * buyCost;
            lineItemsTotal += itemTotal;
            totalQty +=units;
            // $(this).find('.calculated-cost').text(`$${itemTotal.toFixed(2)}`);
        });
        subtotal = (lineItemsTotal)  - preTaxDiscount ;
        var totalSalesTax = (salesTaxRate / subtotal) * 100;
        grandTotal = subtotal + shippingCost + (subtotal/100) * totalSalesTax;
        var preTaxDiscountPcentage = (preTaxDiscount / (subtotal)) * 100;
        var postTaxDiscountPcentage = (postTaxDiscount / (subtotal)) * 100;
        var shippingCostPercentage = (shippingCost / (subtotal + shippingCost)) * 100;
        
        // Apply the sales tax percentage to individual items
        $('#savedItemsContainer tr').each(function() {
            var itemId = $(this).data('item-id'); // Assuming each row has a data attribute for the item ID
            var units = parseFloat($(this).find('.unit-input').val()) || 0;
            var buyCost = parseFloat($(this).find('.cost-input').val()) || 0;
            var itemTotal = units * buyCost;
            var itemSalesTax = (totalSalesTax / 100) * buyCost;
            var preTaxDiscountPcentageew = (preTaxDiscountPcentage / 100) * buyCost;
            var postTaxDiscountPcentageew = (postTaxDiscountPcentage / 100) * buyCost;
            var shippingCostPercentagenew = (shippingCostPercentage / 100) * buyCost;
            buyCost = buyCost + itemSalesTax
            buyCost = buyCost + shippingCostPercentagenew
            buyCost = buyCost - preTaxDiscountPcentageew;
            buyCost = buyCost - postTaxDiscountPcentageew;
             // AJAX request to save data
             var rowData = {
                item_id: itemId,
                pre_discount: preTaxDiscountPcentageew.toFixed(2),
                post_discount: postTaxDiscountPcentageew.toFixed(2),
                shipping_tax: shippingCostPercentagenew.toFixed(2),
            };
            $.ajax({
                url: `{{ url('save-line-item-tax') }}`, // Adjust URL to match your endpoint
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token for security
                    ...rowData,
                },
                success: function (response) {
                    console.log(`Item ${itemId} saved successfully`, response);
                },
                error: function (xhr, status, error) {
                    console.error(`Error saving item ${itemId}:`, error);
                },
            });
            $(this).find('.calculated-cost').text(`$${(buyCost).toFixed(2)}`);
        });
        if(isSalesTaxOnShipping == 1){
            totalSalesTax = (salesTaxRate / (subtotal+shippingCost)) * 100;
            // totalSalesTax = totalSalesTax - shippingCostPercentage
        }
        totalSalesTaxTotal = totalSalesTax;
        grandTotal = grandTotal - postTaxDiscount;
        $('#buyCostCalcPreTaxSubtotal').text(`$${subtotal.toFixed(2)}`);
        $('#buyCostCalcSalesTaxPercentage').text(`${totalSalesTax.toFixed(2)}%`);
        $('#buyCostCalcGrandTotal').html(`<h5>$${grandTotal.toFixed(2)}</h5>`);
    }
    // Function to save the updated item to the database
    function saveUpdatedItem(itemId, units, buyCost, skuTotal) {
        $.ajax({
            url: "{{ url('update-order-item') }}",  // Replace with your actual URL
            method: 'POST',
            data: {
                id: itemId,
                unit_purchased: units,
                buy_cost: buyCost,
                sku_total: skuTotal,
            },
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            success: function(response) {
                if (response.success) {
                    calculateTotals(); // Update grand total after successful save
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
    // Function to retrieve and display all line items for the given order_id
    function loadOrderItems(orderId) {
        $.ajax({
            url: '/get-order-items',  // Replace with your actual URL
            method: 'GET',
            aysnc: false,
            data: { order_id: orderId },  // Pass the order_id as a query parameter
            success: function(response) {
                if (response.success) {
                    // Update the items array with the retrieved items
                    items = response.items;

                    // Display the retrieved items
                    displaySavedItems(items);
                    calculateTotals()

                    console.log('Loaded items:', items); // Debugging: log the loaded items
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
    // Display saved items with calculated costs
    function displaySavedItems(items) {
        $('#savedItemsContainer').empty();
        let calcType = $('#buy_cost_type').val();

        items.forEach(function(item) {
            let calculatedCost = calcType === 'sku'
                ? (item.sku_total / item.unit_purchased).toFixed(2)
                : (item.buy_cost).toFixed(2);
                var bundleItem = '';
                console.log(item.bundles);
                if(item.bundles != null){
                    $.each(item.bundles  ,function(index,val){
                        bundleItem +='<i class="ri-handbag-fill mx-1" title="Bundle" style="font-size:17px;"></i><br>'
                    }) 
                }

            var itemRow = `
                <tr data-id="${item.id}">
                    <td>
                       
                        <div class="d-flex justify-content-between">
                            <span> ${bundleItem}</span>
                            <span>
                                ${item.is_disputed ? `<i class="ri-haze-2-line text-warning ms-1" title="Item data may be disputed"></i>` : ''}
                                ${item.is_hazmat === 1 ? `<i class="ri-alarm-warning-fill text-danger ms-1" title="Hazmat item"></i>` : ''}
                                ${item.asin}
                            </span>
                        
                        </div>
                    </td>
                    <td>${item.name}</td>
                    <td>
                        <div class="col text-center">
                            <input type="number" class="form-control text-center unit-input" value="${item.unit_purchased}" data-id="${item.id}">
                        </div>
                    </td>
                    <td class="single_cost ${calcType === 'sku' ? 'd-none' : ''}">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="buy_cost" step="0.1" class="form-control text-center cost-input" value="${item.buy_cost}" data-id="${item.id}">
                        </div>
                    </td>
                    <td class="total_cost ${calcType === 'individual' ? 'd-none' : ''}">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="sku_total" step="0.1" class="form-control text-center total-cost-input" value="${item.sku_total}" data-id="${item.id}">
                        </div>
                    </td>
                    <td class="bg-light calculated-cost-cell">
                        <div class="d-flex justify-content-between">
                            <span class="calculated-cost">$${calculatedCost}</span>
                            <button class="btn btn-sm btn-outline-danger delete-item-btn" onclick="deleteOrderItem(${item.id})" data-id="${item.id}">
                                <i class="ri-delete-bin-line" aria-hidden="true"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            $('#savedItemsContainer').append(itemRow);
        });
    }
    // Add new order item
    $('#addNewOrderItemBtn').on('click', function() {
        let calcType = $('#buy_cost_type').val();

        var newItemRow = `
            <tr class="new-item-row">
                <td><input type="text" name="asin" class="form-control text-center" required></td>
                <td><input type="text" name="name" class="form-control text-center" required></td>
                <td><input type="number" name="units" min="1" step="1" class="form-control text-center unit-input" value="1" required></td>
                <td class="single_cost ${calcType === 'sku' ? 'd-none' : ''}">
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="buy_cost" min="0.1" step=".01" class="form-control text-center cost-input" value="0" required>
                    </div>
                </td>
                <td class="total_cost ${calcType === 'individual' ? 'd-none' : ''}">
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="sku_total" min="0" step=".01" class="form-control text-center total-cost-input" value="0" required>
                    </div>
                </td>
                <td class="bg-light">
                    <div class="d-flex justify-content-between text-center">
                        <button type="button" class="btn btn-sm btn-primary save-item-btn">Save Item</button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearInputs(this)">
                            <i class="ri-close-circle-line" aria-hidden="true"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        
        $('#orderInputsContainer').append(newItemRow);
        $('#addNewOrderItemBtn').hide();  // Hide add button until the item is saved
    });
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
    }
    function deleteOrderItem(id){
        var itemId = id;
        var parentRow = $('#'+id).remove();
        if (confirm("Are you sure you want to delete this item?")) {
            deleteItemFromDb(itemId, parentRow);
        }
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
    $('.edit-note').click(function() {
        // Toggle between showing the plain text and the textarea
        $('#noteText').toggleClass('d-none');
        $('#noteTextarea').toggleClass('d-none');
        
        // Update the text content when switching back to text mode
        if ($('#noteTextarea').hasClass('d-none')) {
            var updatedNote = $('#noteTextarea').val();
            $('#noteText').text(updatedNote);  // Update the text with the new value
        }
    });
    // Optionally, update the text live while typing (if you want to show updates dynamically)
    $('#noteTextarea').on('input', function() {
        var liveNote = $(this).val();
        $('#noteText').text(liveNote);  // Update the plain text dynamically while typing
    });
    $(document).ready(function () {
        fetchFiles();
        // Listen for changes in any input, select, or textarea
        $('input, select, textarea').on('input change', function () {
            // Show the save button when a change is detected
            $('#saveButton').removeClass('d-none');
        });
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
        // Assuming you have the download URL from Laravel
        var downloadUrl = fullFilePath;
        
        // Create an <a> element
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
        if (!formData['order_id']) { 
            toastr.error('Order ID is required'); // Display error toaster message
            return false; // Prevent further submission
        }
        formData['id'] = orderId;
        formData['total'] = grandTotal;
        formData['subtotal'] = subtotal;
        formData['sales_tax_rate'] = totalSalesTaxTotal;
        formData['total_units_purchased'] = totalQty;
        // formData['total_units_purchased'] = totalQty;
        // Send the data via AJAX
        $.ajax({
            url: "{{ url('save-orders-updated') }}", // Replace with your actual endpoint
            type: 'POST',
            data: formData,
            success: function (response) {
                toastr.success("Data saved successfully!");
                window.location.href = `/order/${orderId}`;
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
    // Initialize with the current selection's background color
    window.onload = changeBackgroundColor;
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
    
</script>
<script>
   
let isFormChanged = false;

// Mark form as changed on any input change
document.addEventListener('DOMContentLoaded', function () {
    const formElements = document.querySelectorAll('input, textarea, select');

    formElements.forEach(function (element) {
        element.addEventListener('change', function () {
            isFormChanged = true;
        });
    });
});
    $('#resetAll').on('click', function() {
       // Trigger beforeunload only if changes exist
        // window.addEventListener('beforeunload', function (e) {
        //     if (isFormChanged) {
        //         e.preventDefault();
        //         e.returnValue = ''; // Required for most browsers
        //     }
        // });
        // window.location.reload(true)
    });

</script>
