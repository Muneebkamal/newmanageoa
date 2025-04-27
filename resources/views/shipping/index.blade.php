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
                    <li class="breadcrumb-item active"><a href="{{ url('shippingbatches') }}">Shipping</a></li>
                </ol>
            </div>

        </div>
    </div>
</div>
<div class="row align-items-center justify-content-between">
    <div class="col-md-12">
        <h3>Shipping Batches</h3>
        <div class="row align-items-center">
            <div class="col-md-auto">
                <button class="btn btn-primary my-2" id="createBtn">Create New</button>
            </div>
            <div class="col-md-auto">
                <select class="form-select" id="statusFilter">
                    <option value="all">ALL ({{ $counts['all'] }})</option>
                    <option value="open">Open ({{ $counts['open'] }})</option>
                    <option value="pending">Pending ({{ $counts['pending'] }})</option>
                    <option value="in_transit">In Transit ({{ $counts['in_transit'] }})</option>
                    <option value="closed">Closed ({{ $counts['closed'] }})</option>
                </select>
            </div>
        </div>

        <!-- Shipping Batch Form -->
        <div class="card mb-0 d-none" style="background: white;" id="create-dev">
            <div class="card-body">
                <form>
                    <div class="row">
                        <div class="col-md-2">
                            <label for="shipping_name">Name</label>
                            <input type="text" id="shipping_name" name="shipping_name" placeholder="New Shipping Batch" autocomplete="off" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="shipping_date">Ship Date</label>
                            <input type="date" id="shipping_date" name="shipping_date" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="market_place">Marketplace</label>
                            <input type="text" id="market_place" name="market_place" placeholder="FBA" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="tracking_number">Tracking Number</label>
                            <input type="text" id="tracking_number" name="tracking_number" placeholder="ABC123" autocomplete="off" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="shipping_notes">Shipping Batch Notes</label>
                            <textarea id="shipping_notes" name="shipping_notes" placeholder="Notes" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="col-md-1 text-right">
                            <button class="btn btn-primary" type="button" onclick="saveShipping()">
                                <i class=" ri-save-3-fill" aria-hidden="true"></i>
                            </button>
                            <button class="btn btn-outline-danger">
                                <i class=" ri-delete-back-2-line" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Pagination and Table -->
        <hr>
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered" id="shippingBatchesTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Status</th>
                            <th>Name</th>
                            <th>Marketplace</th>
                            <th>Shipped Date</th>
                            <th>Tracking Number</th>
                            <th># Items</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
           
        </div>
        
    </div>
</div>
@include('modals.edit-shipping')
@endsection
@section('script')
<script>
    $('#createBtn').on('click',function(){
        $('#create-dev').removeClass('d-none')
        $(this).attr('disabled',true);
    })
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
            },
            success:function(data){
                toastr.success('Record Added successfully'); 
                $('#create-dev').addClass('d-none')
                var shipping_name = $('#shipping_name').val('');
                var shipping_date = $('#shipping_date').val('');
                var market_place = $('#market_place').val('');
                var tracking_number = $('#tracking_number').val('');
                var shipping_notes = $('#shipping_notes').val('');
                $('#shippingBatchesTable').DataTable().ajax.reload(null, false); // Reload DataTable
            }
        })
    }
    $(document).ready(function() {
        var table = $('#shippingBatchesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('shipping.batches') }}", // Replace with your route
                data: function (d) {
                    d.status = $('#statusFilter').val(); // Add the selected status to the request
                }
            },
            columns: [
                { data: 'status', name: 'status' },
                { data: 'name', name: 'name' },
                { data: 'market_place', name: 'market_place' },
                { data: 'date', name: 'date' },
                { data: 'tracking_number', name: 'tracking_number' },
                { data: 'items', name: 'items' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ]
        });
        // Filter the DataTable when the dropdown value changes
        $('#statusFilter').change(function () {
            table.draw(); // Redraw the table with the new filter
        });
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

                    $('#shippingBatchesTable').DataTable().ajax.reload(); // Refresh table
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
    function deleteShipping(id){
        if (!confirm('Are you sure you want to delete this record?')) return;
            $.ajax({
                url: `{{ url('/delete-shippingbatch') }}/${id}`,
                type: 'DELETE',
                success: function (response) {
                    toastr.success('Record deleted successfully'); 
                    $('#shippingBatchesTable').DataTable().ajax.reload(); // Refresh table
                },
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                error: function (error) {
                    alert('An error occurred');
                }
            });
    }
</script>
@endsection