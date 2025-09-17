@extends('layouts.app')

@section('title', 'Dashboard')
@section('content')
    <style>
        #orders-table tbody tr {
            cursor: pointer;
        }
        .dataTables_wrapper .top {
        display: flex;
        align-items: center;
        gap: 20px; /* Space between elements */
        }

        .dataTables_filter {
        margin: 0;
        }

        .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 10px;
        }

        .dataTables_length {
        margin: 0;
        }
    </style>
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Dashboard</h4>
            </div>
        </div>
    </div>
    
    <hr class="mt-3">
    <div class="row">
        
        <div class="col-md-12">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="m-0">Orders For This Week</h4>
                    
                    <div class="d-flex gap-2">
                        <a href="{{ url('orders') }}" class="btn btn-primary">
                            Orders
                        </a>
                        <button id="createNewOrderButton" type="button" class="btn btn-outline-primary">
                            <i class="ri-share-box-fill" aria-hidden="true"></i>&nbsp; Create New Order
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-active table-bordered" id="orders-table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Order ID</th>
                                    <th>Source</th>
                                    <th>O-R-S (E)</th>
                                    <th>Order Date</th>
                                    <th>Order Total</th>
                                </tr>
                            </thead>
                            <tbody>
                               
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>    
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">Leads</h5>
                    <button class="btn btn-primary">
                        Leads
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>ASIN</th>
                                    <th>Purchased</th>
                                    <th>Buy Cost</th>
                                    <th>SKU Total</th>
                                    <th>O-R-S (E)</th>
                                    <th>Product/Buyer Notes</th>
                                    <th>UPC</th>
                                    <th>MSKU</th>
                                    <th>Listing Price</th>
                                    <th>Min</th>
                                    <th>Max</th>
                                    <th>Per Piece Profit</th>
                                </tr>
                            </thead>
                            <tbody id="product-table-body">
                            </tbody>
                        </table>
                    </div>
                </div>
             </div>   
        </div>
    </div> 
    <div class="row mt-3">
        <div class="col-md-12">
           @foreach ($employees as $employee)
            @php
                $source = $employee->source;
                $sourceId = $source?->id;
                $employeeLeads = $leads[$sourceId] ?? collect();
            @endphp

                @if ($source && $employeeLeads->isNotEmpty())
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="m-0">Leads Uploaded Today for {{ $employee->name }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered mt-3">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>ASIN</th>
                                            <th>Source</th>
                                            <th>Sell Price</th>
                                            <th>Cost</th>
                                            <th>Profit</th> 
                                            <th>ROI</th>
                                            
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($employeeLeads as $lead)
                                            <tr>
                                                <td> 
                                                    <a href="{{ url('leads-new') . '?asin=' . $lead->asin }}" target="_blank">
                                                        {{ \Carbon\Carbon::parse($lead->date)->format('Y-m-d') }}    
                                                    </a> 
                                                </td>

                                                <td>{{ $lead->name }}</td>
                                                <td> <a href="https://www.amazon.com/dp/{{  $lead->asin }}" target="_blank">{{ $lead->asin }}</a> </td>
                                                <td> <a href="{{  $lead->url }}">{{ $lead->supplier }}</a> </td>
                                                <td>${{ $lead->sell_price }}</td>
                                                <td>${{ $lead->cost }}</td>
                                                 <td>${{ $lead->net_profit }}</td>
                                                <td>{{ $lead->roi }}%</td>
                                               
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>  
                @endif
            @endforeach

        </div>
    </div> 
@endsection
@section('script')
<script>
    $('#createNewOrderButton').on('click',function(){
        $.ajax({
            url:"{{ url('/create-order') }}",
            type:"POST",
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            success:function(data){
                if(data.success == true){
                    const orderId = data.orderId; // Get the order ID from the response
                    window.location.href = `/list/buycostcalculator/${orderId}`;
                }
            }
        })
    })
    function deleteOrder(id){
        Swal.fire({
            title: 'Are you sure?',
            text: "You WontYou won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, remove this order!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Add your AJAX request here to delete the buylist
                $.ajax({
                    url: `{{ url('/order/${id}/delete') }}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    success: function (response) {
                        // Reload the DataTable
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'The order has been deleted successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                        $('#orders-table').DataTable().ajax.reload(null, false); // Keep the current pagination
                    },
                    error: function (xhr) {
                        toastr.error('delete failed:', xhr); 
                    }
                });
            }
        }); 

    }
    function duplicateOrder(id){
        Swal.fire({
            title: 'Are you sure?',
            text: "This will create a copy of",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, duplicate this order!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Add your AJAX request here to delete the buylist
                $.ajax({
                    url: `{{ url('/order/${id}/duplicate') }}`,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    success: function (response) {
                        // Reload the DataTable
                        if(response.success == true){
                            const orderId = response.new_order_id; // Get the order ID from the response
                            window.location.href = `/list/buycostcalculator/${orderId}`;
                        }
                    },
                    error: function (xhr) {
                        toastr.error('delete failed:', xhr); 
                    }
                });
            }
        }); 

    }
    $(document).ready(function() {
        var table =  $('#orders-table').DataTable({
            processing: true,
            serverSide: true,
            dom: '<"top"lf>rt<"bottom"p><"clear">',
            language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries"
            },
            ajax: {
            url: "{{ route('orders.data') }}", // Replace with your route
                data: function (d) {
                    d.status = $('#orderStatusFilter').val(); // Add the selected status to the request
                    d.sortBy = $('#inputSortSelect').val(); // Get the selected sort field
                    d.orderType = $('#orderbyinput').val();
                    d.type = 'dashboard'  // Get the selected order type
                },
                dataSrc: function (json) {
                    // Fetch line items for each order
                    let orderIds = json.orderIds;
                    // let orderIds = orders.map(order => order.id);

                    if (orderIds.length > 0) {
                        $.ajax({
                            url: "{{ url('/get-order-items-dashboard') }}",
                            type: "GET",
                            data: { order_ids: orderIds },  // Send all order IDs at once
                            success: function (itemsData) {
                                // Append line items to orders
                              $('#ItemTableDev').append(  formatLineItems(itemsData))
                            }
                        });
                    }

                    return json.data;
                }
            },
            columns: [
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'order_id', name: 'order_id' },
                { data: 'source', name: 'source' },
                { data: 'order_item_count', name: 'order_item_count', orderable: false, searchable: false },
                { data: 'date', name: 'date' },
                { data: 'total', name: 'total' },
                // { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
        });
        // Filter the DataTable when the dropdown value changes
        $('#orderStatusFilter ,#inputSortSelect, #orderbyinput').change(function () {
            table.draw(); // Redraw the table with the new filter
        });
        $('#refreshTable').on('click', function () {
            table.ajax.reload(); // Reloads the DataTable via its AJAX source
        });
    });
    function formatLineItems(items) {
        let productTableBody = $('#product-table-body');
        let productCount = $('#product-count');
        productTableBody.empty();
        items.forEach(product => {


            let cost = parseFloat(product.buy_cost) || 0;
            let sellingPrice = parseFloat(product.list_price) || 0;
            let quantity = parseInt(product.unit_purchased) || 1;

            let totalItemCost = cost * quantity;
            let totalItemSelling = sellingPrice * quantity;
            let itemProfit = totalItemSelling - totalItemCost;
            let itemProfitPerPiece =itemProfit/ quantity;

            var orderedItem = product.total_units_purchased != 0 ? `<span class="badge bg-dark" title="Ordered">${product.total_units_purchased}</span>` : '<span title="Ordered">-</span>'
            var receivedItems = product.total_units_received != 0 ? `<span class="badge bg-info" title="received" id="itemTotalReceived${product.id}">${product.total_units_received}</span>` : `<span title="received" id="itemTotalReceived${product.id}">-</span>`
            var shippedItems = product.total_units_shipped != 0 ? `<span class="badge bg-success" title="Shipped" id="itemTotalShipped${product.id}">${product.total_units_shipped}</span>` : `<span title="Shipped" id="itemTotalShipped${product.id}">-</span>`
            var errorItems = product.unit_errors != 0 ? `<span class="badge bg-danger" title="Error" id="itemTotalError${product.id}">${product.unit_errors}</span>` : '<span title="Error" id="itemTotalError'+product.id+'">-</span>';
            sale_text_rate_var =0;
            let percentage =  sale_text_rate_var;
            let value = product.sku_total;
            let totalSalesTax = (percentage / 100) * value;
            var totalNewSkuTotal = product.sku_total + totalSalesTax;
            var newBuyCost = totalNewSkuTotal / product.unit_purchased;
            
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
                <td>
                   <span>${product.unit_purchased}</span>
                </td>
                    <!-- Buy Cost Column with Editable Input -->
                <td ondblclick="showEditableInput(this)">
                    <span class="display-text">$${parseFloat(newBuyCost || 0).toFixed(2)}</span>
                </td>
                <!-- SKU Total Column with Editable Input -->
                <td ondblclick="showEditableInput(this)">
                    <span class="display-text">$${parseFloat(totalNewSkuTotal|| 0).toFixed(2)}</span>
                </td>
                <td>${orderedItem} ${receivedItems} ${shippedItems} ${errorItems}</td>
                    <!-- Product Buyer Notes with Editable Textarea -->
                <td ondblclick="showEditableTextarea(this)">
                    <span class="display-text">${product.product_buyer_notes || 'Add Notes Here'}</span>
                </td>
                <td>${product.upc || '-'}</td>
                <td>${product.msku || '-'}</td>
                <td>$${product.list_price.toFixed(2)}</td>
                <td>$${product.min.toFixed(2)}</td>
                <td>$${product.max.toFixed(2)}</td>
                <td><span class="text-success">$${itemProfit.toFixed(2)}</span></td>
                
            </tr>`;
            productTableBody.append(row);
        });
    }
    $(document).on('click', '#orders-table tbody tr', function (e) {
        // Skip if the clicked element is inside a 'no-click' cell
        if ($(e.target).closest('td.no-click').length) {
            return;
        }

        const url = $(this).data('href');
        if (url) {
            window.open(url, '_blank'); // Open in a new tab
        }
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
</script>
@endsection
