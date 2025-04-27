@extends('layouts.app')
@section('title', 'Shipping Batches')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Buylists</a></li>
                    <li class="breadcrumb-item "><a href="{{ url('orders') }}">Order</a></li>
                    <li class="breadcrumb-item ">Calculator</li>
                    <li class="breadcrumb-item"><a href="{{ url('shippingbatches') }}">Shipping</a></li>
                    <li class="breadcrumb-item active"><a href="{{ url('shippingbatches/',$shipping->id) }}">Shipping Batch</a></li>
                </ol>
            </div>

        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="row mb-3">
            <div class="col-md-auto">
                <h3>Shipping Batch</h3>
                <input type="hidden" name="shipping_id" id="shipping_id" value="{{ $shipping->id }}">
            </div>
            <div class="col-md-auto">
                <button class="btn btn-outline-primary pull-right" onclick="editShipping({{ $shipping->id }})">
                    <i aria-hidden="true" class="fa fa-pencil"></i> Edit
                </button>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-primary">
                    <i aria-hidden="true" class="fa fa-download"></i> Export
                </button>
            </div>
        </div>
    
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3 batch-meta-card">
                    <div class="card-header">
                        <label class="col-form-label">Shipping Batch Details</label>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="form-group row mb-1">
                                <label for="shippingBatchStatus" class="col-md-6">Status</label>
                                <div id="shippingBatchStatus" class="col-md-6 text-center">
                                    @php
                                        // Map the status to appropriate classes and display text
                                        $statusClass = '';
                                        $displayText = '';
                                        switch ($shipping->status) {
                                            case 'open':
                                                $statusClass = 'bg-primary'; // Blue for Open
                                                $displayText = 'Open';
                                                break;
                                            case 'pending':
                                                $statusClass = 'bg-warning'; // Yellow for Pending
                                                $displayText = 'Pending';
                                                break;
                                            case 'close':
                                                $statusClass = 'bg-dark'; // Dark for Close
                                                $displayText = 'Close';
                                                break;
                                            case 'in_transit':
                                                $statusClass = 'bg-success'; // Green for In Transit
                                                $displayText = 'In Transit';
                                                break;
                                            default:
                                                $statusClass = 'bg-secondary'; // Grey for unknown statuses
                                                $displayText = 'Unknown';
                                                break;
                                        }
                                    @endphp

                                    <span class="badge {{ $statusClass }}">{{ $displayText }}</span>
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label for="shippingBatchMarketplace" class="col-md-6">Marketplace</label>
                                <div id="shippingBatchMarketplace" class="col-md-6 text-center">
                                    {{ $shipping->market_place }}
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label for="shippingBatchTotal" class="col-md-6">Total Items</label>
                                <div id="shippingBatchTotal" class="col-md-6 text-center">
                                    0
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label for="shippingBatchTracking" class="col-md-6">Tracking Number</label>
                                <div id="shippingBatchTracking" class="col-md-6 text-center">
                                    {{ $shipping->tracking_number }}
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label for="shippingBatchShipDate" class="col-md-6">Ship Date</label>
                                <div id="shippingBatchShipDate" class="col-md-6 text-center">
                                    {{ $shipping->date }}
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    
            <div class="col-md-6">
                <div class="card mb-3 notes-card">
                    <div class="card-header">
                        <label for="editShippingBatchNote" class="col-form-label">Shipping Batch Notes</label>
                    </div>
                    <div class="card-body">
                        <div class="batch-notes">
                            <span style="white-space: pre;">{{ $shipping->notes }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <hr>
    
        <div class="row">
            <div class="col-md-12">
                <div class="card shipping-table">
                    <div class="card-header">
                        <h5 class="mb-0">Shipping Items</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered" id="shippingEvetns">
                            <thead>
                                <tr>
                                    <th>Added</th>
                                    <th>Purchase Date</th>
                                    <th>ASIN</th>
                                    <th>Title</th>
                                    <th>QTY</th>
                                    <th>Condition</th>
                                    <th>MSKU</th>
                                    <th>Expiration</th>
                                    <th>List Price</th>
                                    <th>Min Price</th>
                                    <th>Max Price</th>
                                    <th>Shipping Note</th>
                                    <th>Actions</th>
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
</div>

@include('modals.edit-shipping')
@include('modals.edit-shipping-event')

@endsection
@section('script')
<script>
    
    $(document).ready(function() {
        shippingEvets()
        // Handle Edit Form Submission
        $('#editForm').on('submit', function (e) {
            e.preventDefault();
            const id = $('#edit_id').val();
            const formData = $(this).serialize();
            $.ajax({
                url: `{{ url('/update-shippingbatch') }}/${id}`,
                type: 'PUT',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                success: function (response) {
                    $('#editModal').modal('hide');
                    toastr.success('Record updated successfully'); 
                    window.location.reload(true);
                    // $('#shippingBatchesTable').DataTable().ajax.reload(); // Refresh table
                },
                error: function (error) {
                    alert('An error occurred');
                }
            });
        });

       
    });
    function editShipping(id){
        $.get('/shippingbatch/' + id + '/edit', function (data) {
            $('#edit_id').val(data.id);
            $('#edit_status').val(data.status).trigger('change');
            $('#edit_name').val(data.name);
            $('#edit_market_place').val(data.market_place);
            $('#edit_date').val(data.date);
            $('#edit_tracking_number').val(data.tracking_number);
            $('#edit_notes').val(data.notes);
            // $('#edit_quantity').val(data.quantity);
            $('#editModal').modal('show');
        });
    }
    function shippingEvets(){
        var id = $('#shipping_id').val();
        $.ajax({
            url:`{{ url('get-shipping/${id}/events') }}`,
            type:"GET",
            success:function(data){
                console.log(data);
                data.forEach(item => {
                        // Create the HTML for a table row
                        let newRow = `
                            <tr>
                                <td>${formatDateWithMoment(item.order.created_at)  }</td>
                                <td>${ formatDateWithMoment(item.order_item.created_at)}</td>
                                <td>${item.order_item.asin}</td>
                                <td>${item.product_name_override}</td>
                                <td>${item.items}</td>
                                <td>${item.condition}</td>
                                <td>${item.msku_orderride}</td>
                                <td>${formatDateWithMoment(item.expire_date)}</td>
                                <td>$${item.list_price_orverride}</td>
                                <td>$${item.min_orverride}</td>
                                <td>$${item.max_orverride}</td>
                                <td>${item.shipping_notes}</td>
                                <td>
                                <button id="dropdownActions6421" class="btn btn-sm btn-outline-danger float-end" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="small-screen-hide"></span>
                                <i class="mdi mdi-dots-vertical" aria-hidden="true"></i>
                                </button>
                                <!-- Dropdown Menu -->
                                <ul class="dropdown-menu" aria-labelledby="dropdownActions6421">
                                    <!-- Add your dropdown items here -->
                                    <li><a class="dropdown-item text-info" href="javascript:editShippingEvent(${item.id},${item.order_item_id})"><i class=" ri-folder-open-fill"></i> Edit Shipping Event</a></li>
                                <li><a class="dropdown-item text-danger" href="javascript:deleteShippingEvent(${item.id},${item.order_item_id})"><i class=" ri-delete-bin-fill"></i> Delete Shipping Event</a></li>
                                </ul>
                            
                            </td>
                            </tr>
                        `;
                        // Append the new row to the table
                        $('#shippingEvetns tbody').append(newRow);
                    });
          
            }   
        })
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
                $('#ship_event_id').val(data.id);
                $('#itemsToShip').val(data.items);
                $('#asinOverride').val(data.asin_override);
                $('#expirationDate').val(data.expire_date);
                $('#titleOverride').val(data.product_name_override);
                $('#condition').val(data.condition);
                $('#mskuOverride').val(data.msku_orderride);
                $('#listPrice').val(data.list_price_orverride);
                $('#minOverride').val(data.min_orverride);
                $('#maxOverride').val(data.max_orverride);
                $('#upcOverride').val(data.product_upc);
                $('#shippingNotes').val(data.shipping_notes);
                data.description_matches_flag == 1?$('#descriptionMatchesFlag').prop('checked',true):$('#descriptionMatchesFlag').prop('checked',false); 
                data.title_matches_flag == 1?$('#titleMatchesFlag').prop('checked',true):$('#titleMatchesFlag').prop('checked',false);
                data.upc_matches_flag == 1?$('#upcMatchesFlag').prop('checked',true):$('#upcMatchesFlag').prop('checked',false);
                data.image_matches_flag == 1?$('#imageMatchesFlag').prop('checked',true):$('#imageMatchesFlag').prop('checked',false);
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
</script>
@endsection