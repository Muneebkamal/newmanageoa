@extends('layouts.app')

@section('title', 'Orders')

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
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Buylists</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('orders') }}">Order</a></li>
                        {{-- <li class="breadcrumb-item ">Calculator</li>
                        <li class="breadcrumb-item "><a href="{{ url('shippingbatches') }}">Shipping</a></li> --}}
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <div data-v-c2ce40f4="" class="row">
        <h1 class="card-title">Orders</h1>
        <div data-v-c2ce40f4="" class="col-md-auto">
            <select data-v-c2ce40f4="" class="form-select" id="orderStatusFilter">
                <option data-v-c2ce40f4="" value="all">ALL ({{ $counts['all'] }})</option>
                <option data-v-c2ce40f4="" value="draft">Draft ({{ $counts['draft'] }})</option>
                <option data-v-c2ce40f4="" value="ordered">Ordered ({{ $counts['ordered'] }})</option>
                <option data-v-c2ce40f4="" value="partially received">Partially Received ({{ $counts['partially_received'] }})</option>
                <option data-v-c2ce40f4="" value="reconcile">Reconcile ({{ $counts['reconcile'] }})</option>
                <option data-v-c2ce40f4="" value="received in full">Received in Full ({{ $counts['received_in_full'] }})</option>
                <option data-v-c2ce40f4="" value="closed">Closed ({{ $counts['closed'] }})</option>
                <option data-v-c2ce40f4="" value="canceled">Canceled ({{ $counts['canceled'] }})</option>
            </select>
        </div>        
        <div data-v-c2ce40f4="" class="col-md-auto">
            <div class="input-group input-group-sm">
                <div class="input-group-prepend">
                    <span class="input-group-text font-weight-bold">Sort By</span>
                </div>
                <select id="inputSortSelect" class="form-select">
                    <option value="">Choose...</option>
                    <option value="created_at">Created Date</option>
                    <option value="date"selected>Order Date</option>
                    <option value="order_id">Order ID</option>
                    <option value="source">Source</option>
                    <option value="total">Order Total</option>
                    <option value="status">Status</option>
                </select>
                <select id="orderbyinput" class="form-select custom-select-sm">
                    <option value="asc">Oldest to Newest</option>
                    <option value="desc" selected>Newest to Oldest</option>
                </select>
            </div>
            
        </div>
        <div data-v-c2ce40f4="" class="col-md-auto d-none">
            <div data-v-c2ce40f4="" class="col-auto px-1" style="justify-content: left;">
                <button data-v-c2ce40f4="" class="btn btn-sm btn-outline-primary" data-original-title="" title="" id="refreshTable"><i data-v-c2ce40f4="" aria-hidden="true" class=" ri-refresh-fill"></i>&nbsp; Refresh Data</button>
            </div>
        </div>
        <div class="col-md-auto">
            <form class="d-flex align-items-center">
                <div class="form-group d-flex align-items-center mr-3">
                    <label for="UserDownloadOrderItemStartDate" class="mr-2 mb-0">Start</label>
                    <input type="date" id="UserDownloadOrderItemStartDate" class="form-control">
                </div>
                <div class="form-group d-flex align-items-center mr-3">
                    <label for="UserDownloadOrderItemEndDate" class="mr-2 mb-0">End</label>
                    <input type="date" id="UserDownloadOrderItemEndDate" class="form-control">
                </div>
                <button type="button" id="downloadOrdersBtn" class="btn btn-primary ms-1">
                    <i class="fa fa-download"></i> Download Orders
                </button>
            </form>
        </div>
    </div>
    <hr class="mt-3">
    <div class="row">
        <div class="col-md-12">
            <div class="card h-100">
                <div class="card-header">
                    <div data-v-c2ce40f4="" class="col-md-3 pl-0 float-end">
                        <button data-v-c2ce40f4="" id="createNewOrderButton" type="button" class="btn btn-outline-primary ml-0 float-right"><i data-v-c2ce40f4="" aria-hidden="true" class=" ri-share-box-fill"></i>&nbsp; Create New Order</button></div>
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
                                    <th></th>
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
                    d.orderType = $('#orderbyinput').val();  // Get the selected order type
                }
            },
            columns: [
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'order_id', name: 'order_id' },
                { data: 'source', name: 'source' },
                { data: 'order_item_count', name: 'order_item_count', orderable: false, searchable: false },
                { data: 'date', name: 'date' },
                { data: 'total', name: 'total' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
         // Filter the DataTable when the dropdown value changes
        $('#orderStatusFilter ,#inputSortSelect, #orderbyinput').change(function () {
            table.draw(); // Redraw the table with the new filter
        });
        $('#refreshTable').on('click', function () {
            table.ajax.reload(); // Reloads the DataTable via its AJAX source
        });

        
    });
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    document.addEventListener('DOMContentLoaded', function () {
        const today = new Date();
        // Get the date 10 days ago
        const tenDaysAgo = new Date(today);
        tenDaysAgo.setDate(today.getDate() - 10);

        // Format date as YYYY-MM-DD (local time)
        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        // Set the default values for start and end dates (last 10 days)
        document.getElementById('UserDownloadOrderItemStartDate').value = formatDate(tenDaysAgo);
        document.getElementById('UserDownloadOrderItemEndDate').value = formatDate(today);
        
        // Set the max value for the end date to today's date
        document.getElementById('UserDownloadOrderItemEndDate').setAttribute('max', formatDate(today));
    });

    $(document).on('click', '#downloadOrdersBtn', function () {
        const startDate = $('#UserDownloadOrderItemStartDate').val();
        const endDate = $('#UserDownloadOrderItemEndDate').val();

        if (!startDate || !endDate) {
            alert('Please select both start and end dates.');
            return;
        }

        $.ajax({
            url: `{{ url('download-orders') }}`,
            method: 'POST',
            data: {
                start_date: startDate,
                end_date: endDate,
                _token: $('meta[name="csrf-token"]').attr('content') // Include CSRF token
            },
            xhrFields: {
                responseType: 'blob' // To handle the Excel file download
            },
            success: function (response) {
                // Create a link element, set it to the blob data, and trigger download
                const blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'orders.csv';
                document.body.appendChild(a);
                a.click();
                a.remove();
            },
            error: function (error) {
                console.error('Error downloading orders:', error);
                alert('An error occurred while trying to download orders. Please try again.');
            }
        });
    });
    $(document).on('click', '#orders-table tbody tr', function (e) {
        // Get the clicked td
        const clickedTd = $(e.target).closest('td');

        // Skip if it's a 'no-click' td or the last td in the row
        if (clickedTd.hasClass('no-click') || clickedTd.is(':last-child')) {
            return;
        }

        const url = $(this).data('href');
        if (url) {
            window.open(url, '_blank'); // Open in a new tab
        }
    });





</script>
@endsection
