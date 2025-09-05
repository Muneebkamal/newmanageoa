@extends('layouts.app')

@section('title', 'Add Employee')

@section('content')
<style>
    #orders-table {
        width: 100% !important;
    }
</style>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="report-tab" data-bs-toggle="tab" data-bs-target="#report" type="button" role="tab" aria-controls="report" aria-selected="true">Report</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab" aria-controls="orders" aria-selected="false">Orders</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="buylist-tab" data-bs-toggle="tab" data-bs-target="#buylist" type="button" role="tab" aria-controls="buylist" aria-selected="false">Buylist</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content pt-3" id="myTabContent">
                        <div class="tab-pane fade show active" id="report" role="tabpanel" aria-labelledby="report-tab">
                        <h2>Lead Upload Summary for {{ $employee->name }}</h2>
                            <p>
                                <strong>Total Leads Inserted:</strong> {{ $leads->count() }}  
                                <strong>Date:</strong> {{ $end->format('Y-m-d') }}
                            </p>
                            @if($leads->isNotEmpty())
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>ASIN</th>
                                            <th>Source</th>
                                            <th>Cost</th>
                                            <th>Sell Price</th>
                                            <th>Profit</th>
                                            <th>ROI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($leads as $lead)
                                            <tr>
                                                <td>
                                                    <a href="{{ url('leads-new?asin='.$lead->asin) }}" target="_blank">
                                                        {{ \Carbon\Carbon::parse($lead->created_at)->timezone('America/New_York')->format('Y-m-d h:i A') }}
                                                    </a>
                                                </td>
                                                <td>{{ $lead->name }}</td>
                                                <td>
                                                    <a href="https://www.amazon.com/dp/{{ $lead->asin }}" target="_blank">{{ $lead->asin }}</a>
                                                </td>
                                                <td>
                                                    <a href="{{ $lead->url }}" target="_blank">{{ $lead->supplier }}</a>
                                                </td>
                                                <td>${{ $lead->cost }}</td>
                                                <td>${{ $lead->sell_price }}</td>
                                                <td>${{ $lead->net_profit }}</td>
                                                <td>{{ $lead->roi }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p>No leads uploaded in this period.</p>
                            @endif
                            @if($extraLeads->isNotEmpty())
                                <hr>
                                <h3>Leads Not in Buylist & Not Rejected</h3>
                                <p><strong>Last {{ $extraLeads->count() }} Leads</strong></p>

                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>ASIN</th>
                                            <th>Source</th>
                                            <th>Cost</th>
                                            <th>Sell Price</th>
                                            <th>Profit</th>
                                            <th>ROI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($extraLeads as $lead)
                                            <tr>
                                                <td><a href="{{ url('leads-new?asin='.$lead->asin) }}" target="_blank">
                                                    {{ \Carbon\Carbon::parse($lead->created_at)->timezone('America/New_York')->format('Y-m-d h:i A') }}
                                                </a></td>
                                                <td>{{ $lead->name }}</td>
                                                <td><a href="https://www.amazon.com/dp/{{ $lead->asin }}" target="_blank">{{ $lead->asin }}</a></td>
                                                <td><a href="{{ $lead->url }}" target="_blank">{{ $lead->supplier }}</a></td>
                                                <td>${{ $lead->cost }}</td>
                                                <td>${{ $lead->sell_price }}</td>
                                                <td>${{ $lead->net_profit }}</td>
                                                <td>{{ $lead->roi }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="orders" role="tabpanel" aria-labelledby="orders-tab">
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
                        <div class="tab-pane fade" id="buylist" role="tabpanel" aria-labelledby="buylist-tab">
                           <div class="table-responsive">
                                <table id="buylistTable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>
                                                <div class="btn-group btn-group1">
                                                    <input type="checkbox" class="checkAll" value="">
                                                    <div class="cursor-pointer d-none bulk-action-dropdown"style="" >

                                                        <span class="d-none bulk-action-dropdown mt-1" id="totalCheckedCount"></span>
                                                    <a style="cursor: pointer" data-bs-toggle="dropdown" aria-expanded="false" class="d-none bulk-action-dropdown">
                                                        <strong style="font-size: 17px;"><b><i class="ri-more-2-line ms-2"></i></b></strong>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <button class="dropdown-item text-secondary createOrder" data-id="">
                                                                <i class=" ri-share-box-fill text-dark"></i>Create New Order
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item text-secondary moveCopyToBuylist" data-id="">
                                                                <i class=" ri-arrow-go-forward-fill text-secondary"></i>Move/Copy to Buylist...
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item text-danger deleteAllItem" data-id="">
                                                                <i class="ri-delete-bin-line text-danger"></i> Delete
                                                            </button>
                                                        </li>
                                                    </ul>
                                                    </div>
                                                </div>
                                            </th>
                                            <th class="bg-danger text-white">Rejcation Reason</th>
                                            <th>Type</th>
                                            <th>Created Date</th>
                                            <th>Source</th>
                                            <th>Product Name</th>
                                            <th>Lead Notes</th>
                                            <th>ASIN</th>
                                            <th>Qty</th>
                                            <th>Cost/Unit</th>
                                            <th>Promo/Coupon</th>
                                            <th>Product/Buyer Notes</th>
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
            <!-- Tab contents -->
            

            <div class="card">
               
            </div>
        </div>
    </div>
@include('modals.add-new-buylist')
    @include('modals.add-lead-buylist')
    @include('modals.edit-lead-buylist')
    @include('modals.reject-modal')
    @include('modals.move-lead-modal')
    
    <!-- Rename Modal -->
<div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renameModalLabel">Rename <span id="oldName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="buylistNewName" class="form-control" placeholder="Enter new name">
                <input type="hidden" id="buyListID" class="form-control" placeholder="Enter new name">
              <label for=""> No special characters (*!@#$%^&)</label>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">Close</button>
                <button type="button" class="btn btn-primary" id="saveBuylistName">Save changes</button>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="selectedbuylistID" id="selectedbuylistID">
@endsection

@section('script')
<script>
    var buylist = {!! json_encode($buylist) !!}
    loadBuylistData()
    
    $('#asin').on('input',function(){
        $('.amazonUrl').attr('href','https://www.amazon.com/dp/'+$(this).val()+'')
    })
    $('#editOrderAsin').on('input',function(){
        $('#editAmazonUrl').attr('href','https://www.amazon.com/dp/'+$(this).val()+'')
    })
    $('#url').on('input',function(){
        $('.source_url').attr('href',$(this).val())
    })
    $('#orderSourceUrl').on('input',function(){
        $('.source_url').attr('href',$(this).val())
    })
    $('#editOrderSourceUrl').on('input',function(){
        $('.edit_source_url ').attr('href',$(this).val())
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
            unit_purchased: $('#quantity').val(),
            order_id: orderId,
            list_price: $('#listPrice').val(),
            msku: $('#msku').val(),
            product_buyer_notes: $('#orderNote').val(),
            min: $('#minPrice').val(),
            max: $('#maxPrice').val(),
            name: $('#orderName').val(),
            asin: $('#orderAsin').val(),
            category: $('#orderCategory').val(),
            supplier: $('#orderSupplier').val(),
            source_url: $('#orderSourceUrl').val(),
            order_note: $('#orderProductNote').val(),
            buy_cost: $('#orderCost').val(),
            selling_price: $('#orderSellingPrice').val(),
            bsr: $('#orderBsr').val(),
            promo: $('#orderPromo').val(),
            coupon_code: $('#coupon_code').val(),
            isHazmat: $('#orderIsHazmat').is(':checked'),
            isDisputed: $('#orderIsDisputed').is(':checked')
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
                // Handle success response
                if(response.success){
                    toastr.success('item saved successfully!');
                    loadOrderItems(orderId)

                    // window.open(`list/buycostcalculator/${response.id}`, '_blank');
                }else{
                    toastr.success('Order and item saved successfully!');
                }
            },
            error: function(error) {
                // Handle error response
                alert('An error occurred. Please try again.');
            }
        });
    });
    var startDateFromURL = '';
    var endDateFromURL = '';
    var user_id = '';
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        startDateFromURL = urlParams.get('start_date');
        endDateFromURL = urlParams.get('end_date');
        user_id = urlParams.get('user_id');

        $('.checkAll').on('change', function() {
            // Check or uncheck all checkboxes
            $('input[name="leadCheckBox"]').prop('checked', $(this).prop('checked'));
            
            // Show or hide the bulk action dropdown based on selection
            toggleBulkActionDropdown();
        });
        $('#saveBuylistName').on('click', function() {
            const newName = $('#buylistNewName').val();
            // const buylistId = $('#renameModal').data('buylistId');
            const buylistId = $('#buyListID').val();
            
            // Add your AJAX request here to save the new name
            $.ajax({
                url: "{{ url('rename-buylist') }}", // Replace with your API endpoint
                method: 'POST',
                data: { id: buylistId, name: newName },
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                success: function(response) {
                    toastr.success('Buylist updated successfully!');
                    $('#renameModal').modal('hide'); // Hide the modal
                    const buylistItem = $('.buyListsDev').find(`[data-buylist-id="${buylistId}"]`);
                    buylistItem.find('strong').text(newName); // Update the displayed name
                    // Optionally, update the UI with the new name
                },
                error: function(error) {
                    // Handle error
                }
            });
        });
        $('#createBuylistButton').click(function() {
            const buylistName = $('#listNameInput').val().trim();
            if (buylistName === '') {
                alert('Please enter a buylist name.');
                return;
            }
            $.ajax({
                url: "{{ url('save-buylist') }}",
                type: 'POST',
                data: {
                    name: buylistName,
                },
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Buylist created successfully!');
                        $('#modal-team-create-buylist').modal('hide');
                        $('#listNameInput').val('');
                        loadBuylists();
                    } else {
                        toastr.error(response.message || 'An error occurred while creating the buylist.');
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + error);
                }
            });
        });

    });

    function singleChecked(id){
        if ($('input[name="leadCheckBox"]:checked').length === $('input[name="leadCheckBox"]').length) {
            $('.checkAll').prop('checked', true);
        } else {
            $('.checkAll').prop('checked', false);
        }
        // Show or hide the bulk action dropdown based on selection
        toggleBulkActionDropdown();
    }
    function toggleBulkActionDropdown() {
        if ($('input[name="leadCheckBox"]:checked').length > 1) {
            $('.bulk-action-dropdown').removeClass('d-none'); // Show bulk actions dropdown
            $('#totalCheckedCount').text($('input[name="leadCheckBox"]:checked').length)
            $('.checkAll').addClass('d-none')
        } else {
            $('.bulk-action-dropdown').addClass('d-none'); // Hide bulk actions dropdown
            $('.checkAll').removeClass('d-none')
        }
    }
    $('#totalCheckedCount').on('click',function(){
        $('.checkAll').prop('checked', false);
        $('.checkAll').removeClass('d-none')
        $('.bulk-action-dropdown').addClass('d-none');
        $('input[name="leadCheckBox"]:checked').prop('checked', false);
    })

    function loadBuylistData(buylistId=null) {
        var is_rejected = $('#is_rejected_yes').val();
        $('#buylistTable').DataTable().destroy();
            var columns = [
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
                { data: 'rejection_reason', name: 'rejection_reason', visible: is_rejected == 1,className: 'rejection-reason text-white' }, // Set visibility based on is_rejected
                { data: 'flags', name: 'flags', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'source_url', name: 'source_url'},
                { data: 'name', name: 'name' },
                { data: 'order_note', name: 'order_note' },
                { data: 'asin', name: 'asin'},
                { data: 'unit_purchased', name: 'unit_purchased' },
                { data: 'buy_cost', name: 'buy_cost'},
                { data: 'quantity_remaining', name: 'quantity_remaining', defaultContent: '--', orderable: false, searchable: false },
                { data: 'product_buyer_notes', name: 'product_buyer_notes' }
            ];
        $('#buylistTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: `{{ url('buylists/items') }}`,
                type: 'GET',
                data: {
                    is_rejected: is_rejected, // Replace 'someParameter' with the name of the parameter you want to send
                    is_approved:true,
                    start_date : startDateFromURL,
                    end_date : endDateFromURL,
                    user_id :user_id,
                },
                dataSrc: function(json) {
                    // Check the rejected count in the response and toggle the rejected button
                    if(is_rejected == 0){
                        if (json.rejectedCount > 0) {
                            $('#rejectedButton').removeClass('d-none');
                            $('#countRejected').text(`(${json.rejectedCount})`)
                        } else {
                            $('#countRejected').text(`(${json.rejectedCount})`)
                            $('#rejectedButton').addClass('d-none');
                        }
                    }
                    
                    return json.data; // Return the actual data for DataTable
                },
            },
            columns: columns,
            order: [[3, 'desc']], // Order by created_at by default
            destroy: true,
            autoWidth: false,  // Disable auto width to enable the specified width
            width: '100%',     // Set the table width to 100%
            // Add the event listener to handle data length check
            drawCallback: function (settings) {
                // Check the displayed row count on each draw
                var rowCount = this.api().rows({ page: 'current' }).count();
                if (rowCount === 0) {
                    $('#tableCard').addClass('d-none');
                    $('#addNewCard').removeClass('d-none');
                } else {
                    $('#tableCard').removeClass('d-none');
                    $('#addNewCard').addClass('d-none');
                }
            }
        });
    }

    function renameBuyList(id,name){
        // const buylistId = $(this).data('name'); // Get the buylist ID
        $('#buyListID').val(id); // Store the ID in the modal
        // $('#buylistNewName').val(name); // Store the ID in the modal
        $('#oldName').text(name); // Store the ID in the modal
        // $('#renameModal').data('buylistId', name); // Store the ID in the modal
        $('#renameModal').modal('show'); // Show the modal
    }
    function deleteBuyList(id){
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
                    url: "{{ url('delete-buylist') }}", // Replace with your API endpoint
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    data: { id: id },
                    success: function(response) {
                        Swal.fire('Deleted!', 'Your buylist has been deleted.', 'success');
                        loadBuylists()
                        // Optionally, remove the item from the UI
                        // $(`.delete-buylist[data-name="${buylistId}"]`).closest('.dropdown-item').remove();
                    },
                    error: function(error) {
                        // Handle error
                    }
                });
            }
        });

    }
    function loadBuylistsForModal() {
        fetch('{{ url("get-buylists") }}')
        .then(response => response.json())
        .then(buylists => {
        document.querySelectorAll('.buylist-dropdown').forEach(buylistDropdown => {
            buylistDropdown.innerHTML = ''; // Clear previous entries

            let hasTeamBuy = false;
            let activeBuylist = null;
            let activeID = null;
            buylists.forEach(buylist => {
            const isTeamBuylist = buylist.name === 'Team Buylist';
            if (isTeamBuylist) hasTeamBuy = true;
                // Check if this buylist is the active one
                const buylistElement = document.querySelector(`[data-buylist-id="${buylist.id}"] span`);
    
                if (buylistElement) {
                    const isActive = buylistElement.classList.contains('buylist_active');                
                    if (isActive) {
                        activeBuylist = buylist.name; // Save the active buylist name
                        activeID = buylist.id; // Save the active buylist name
                        console.log(activeID);
                    }
                }
            // Create a dropdown item for each buylist
            const buylistItem = document.createElement('li');
            buylistItem.innerHTML = `
                <a class="dropdown-item buylist-option" href="#" data-buylist-name="${buylist.name}" data-buylistSelect-id="${buylist.id}">
                Add to ${buylist.name}
                </a>`;
            buylistDropdown.appendChild(buylistItem);
            });        
            // Set default selected buylist to the active one or "Team Buylist" if it exists
            const defaultBuylist = activeBuylist || (hasTeamBuy ? 'Team Buylist' : buylists[0]?.name);
            activeID = activeID ||  buylists[0]?.id;
            selectDefaultBuylistModal(buylistDropdown.closest('.buylist-group'), defaultBuylist);
            console.log(activeID);
            $('#selectedbuylistID').val(activeID)
        });

        // Initialize click handlers for each dropdown item
        initializeBuylistClickHandlerModal();
        })
        .catch(error => {
            console.error('Error fetching buylists:', error);
        });
    }

    function selectDefaultBuylistModal(buylistGroup, buylistName) {
    const buylistButton = buylistGroup.querySelector('.buylist-button');
    if (buylistButton) {
        buylistButton.textContent = 'Add to '+ buylistName; // Set dropdown button text to the selected buylist
    }
    }

    function initializeBuylistClickHandlerModal() {
        document.querySelectorAll('.buylist-option').forEach(item => {
            item.addEventListener('click', function (e) {
            e.preventDefault();
            const buylistName = this.getAttribute('data-buylist-name');
            const buylistSelectId = this.getAttribute('data-buylistSelect-id');
            console.log(buylistSelectId);
            $('#selectedbuylistID').val(buylistSelectId)
            
            // Remove 'active' class from all options and add it to the selected one
            document.querySelectorAll('.buylist-option').forEach(option => {
                option.classList.remove('active');
            });
            this.classList.add('active');

            // Update the text for all .buylist-button elements within the modal
            document.querySelectorAll('.buylist-button').forEach(buylistButton => {
                buylistButton.textContent = 'Add to ' + buylistName; // Update button text for each instance
            });
            });
        });
    }

    function opendLeadModal(){
        $('#buyListLeadModal').modal('show');
        loadBuylistsForModal()
    }

    function saveBuylistData() {
        // Get selected buylist ID from the dropdown
        const selectedBuylist = document.querySelector('.buylist-option.active'); // Assuming 'active' is added when selected
        const buylistId = $('#selectedbuylistID').val();
        const buyListLeadData = {
            unit_purchased: $('#quantity').val(),
            buylist_id: buylistId,
            list_price: $('#listPrice').val(),
            msku: $('#msku').val(),
            product_buyer_notes: $('#orderNote').val(),
            min: $('#minPrice').val(),
            max: $('#maxPrice').val(),
            name: $('#orderName').val(),
            asin: $('#orderAsin').val(),
            category: $('#orderCategory').val(),
            supplier: $('#orderSupplier').val(),
            source_url: $('#orderSourceUrl').val(),
            order_note: $('#orderProductNote').val(),
            buy_cost: $('#orderCost').val(),
            selling_price: $('#orderSellingPrice').val(),
            bsr: $('#orderBsr').val(),
            promo: $('#orderPromo').val(),
            coupon_code: $('#coupon_code').val(),
            is_hazmat: $('#orderIsHazmat').is(':checked'),
            is_disputed: $('#orderIsDisputed').is(':checked')
        };
        // Send the data to the backend
        $.ajax({
            url: "{{ url('save-buylist-data') }}",  // Replace with the correct URL
            type: 'POST',
            data: buyListLeadData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            success: function(response) {
                toastr.success('Buylist lead added successfully!');
                $('#buyListLeadModal').modal('hide');
                loadBuylistData(buylistId)
                // Optional: Refresh or update the page based on response
            },
            error: function(xhr) {
                console.error('Error saving data:', xhr.responseText);
            }
        });
    }
    // Delete Button Functionality
    $(document).on('click', '.deleteItem', function () {
        const itemId = $(this).data('id'); // Get the item ID from data-id
        Swal.fire({
            title: 'Are you sure?',
            text: "This action will delete the item permanently!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('/items/${itemId}/delete') }}`, // Use the item ID in the URL
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    success: function (response) {
                        Swal.fire('Deleted!', 'The item has been deleted.', 'success');
                        const buylistId = $('#selectedbuylistID').val();
                        loadBuylistData(buylistId)
                        // Reload table or update UI if necessary
                    },
                    error: function () {
                        Swal.fire('Error', 'There was an error deleting the item.', 'error');
                    }
                });
            }
        });
    });
    
    let buyCost = 0;
    let sellingPrice = 0;
    let netProfit = 0;
    $(document).on('click', '.editItem, .approvelItem', function () {
        const itemId = $(this).data('id');
        const isViewOnly = $(this).data('viewonly') === true || $(this).data('viewonly') === "true";
        // Set modal mode
        $('#modalMode').val(isViewOnly ? 'approved' : 'edit');
        if(isViewOnly){
            $('#editBuyListLeadModal .save-button').text('Approved'); // Hide the footer buttons
        }else{
            $('#editBuyListLeadModal .save-button').text('Save'); // Hide the footer buttons
        }
        $.ajax({
            url: `/items/${itemId}/edit`,
            type: 'GET',
            success: function (data) {
                // Populate the fields as usual
                $('#editOrderName').val(data.name);
                $('#itemIdEdit').val(data.id);
                $('#editOrderAsin').val(data.asin).trigger('input');
                $('#editOrderCost').val(data.buy_cost);
                $('#editOrderNote').val(data.product_buyer_notes);
                $('#editListPrice').val(data.list_price);
                $('#editOrderSellingPrice').val(data.selling_price);
                // Check if net_profit is null or 0, then calculate it
                let netProfit = data.net_profit;
                if (netProfit === null || netProfit == 0) {
                    let cost = parseFloat(data.buy_cost) || 0;
                    let sellingPrice = parseFloat(data.selling_price) || 0;
                    netProfit = sellingPrice - cost;
                    netProfit = netProfit.toFixed(2);
                }

                $('#editOrderNetProfit').val(netProfit);
                $('#editOrderBsr').val(data.bsr);
                $('#editQuantity').val(data.unit_purchased);
                $('#editBuyListLeadModal #quantity').val(data.unit_purchased);
                $('#editOrderProductNote').val(data.order_note);
                $('#editOrderCategory').val(data.category);
                // console.log(data.unit_purchased * data.buy_cost);
                // console.log(data.unit_purchased * data.selling_price);
                // $('.order-qty-cost').html('$' + (data.unit_purchased * data.buy_cost).toFixed(2));
                // $('.order-qty-selling').html('$' + (data.unit_purchased * parseFloat(data.selling_price)).toFixed(2));
                // $('.order-qty-gross-profit').html('$' + (data.unit_purchased * parseFloat(netProfit)).toFixed(2));
                appendTotalHtl(data.buy_cost,data.unit_purchased,netProfit,data.selling_price)

                buyCost = data.buy_cost;
                sellingPrice = data.selling_price;
                netProfit = netProfit;

                let supplier = data.supplier;
                if (supplier && !supplier.includes('.com')) {
                    supplier += '.com';
                }
                let domain = data.source_url;
                if (data.source_url) {
                    try {
                        domain = new URL(data.source_url).hostname;
                    } catch (e) {
                        console.error('Invalid URL:', data.source_url);
                    }
                }
                $('#editOrderSupplier').val(domain);

                $('#editOrderSourceUrl').val(data.source_url);
                $('#editOrderPromo').val(data.promo);
                $('#editCouponCode').val(data.coupon_code || '');
                $('#createdBy').text(data.created_by?.name || '');

                $('#editAmazonUrl').attr('href', 'https://www.amazon.com/dp/' + data.asin);
                $('.edit_source_url').attr('href', data.source_url);

                $('#editOrderIsHazmat').prop('checked', data.is_hazmat === 1);
                $('#editOrderIsDisputed').prop('checked', data.is_disputed === 1);

                $('#editMinPrice').val(data.min);
                $('#editMaxPrice').val(data.max);
                $('#editMsku').val(data.msku);

                // If it's view-only, disable all inputs
                if (isViewOnly) {
                    $('#editBuyListLeadModal input, #editBuyListLeadModal textarea, #editBuyListLeadModal select').prop('disabled', true);
                    $('#editBuyListLeadModal input[type=checkbox]').prop('disabled', true);
                } else {
                    $('#editBuyListLeadModal input, #editBuyListLeadModal textarea, #editBuyListLeadModal select').prop('disabled', false);
                    $('#editBuyListLeadModal input[type=checkbox]').prop('disabled', false);
                }

                $('#editBuyListLeadModal').modal('show');
            }
        });
    });


    function changeQty(type) {
        let qty =  parseInt($('#editQuantity').val()) || 1;
        if (type === 'plus') {
            qty += 1;
        } else if (type === 'minus' && qty > 1) {
            qty -= 1;
        }

        $('#editBuyListLeadModal #quantity').val(qty);
        updatePriceSpans(qty); // just update display
    }

    function updatePriceSpans(qty) {
        // console.log(qty, buyCost, sellingPrice, netProfit);
        // netProfit = parseFloat($("#editOrderNetProfit").val())
        // $('.order-qty-cost').text('$' + (qty * buyCost).toFixed(2));
        // $('.order-qty-selling').text('$' + (qty * sellingPrice).toFixed(2));
        // $('.order-qty-gross-profit').text('$' + (qty * netProfit).toFixed(2));
        let quantity_new = qty;
        let cost = buyCost;
        let sellPricenew = sellingPrice;
        let formattedPrice = parseFloat(sellPricenew).toFixed(2);
        appendTotalHtl(cost,quantity_new,netProfit,formattedPrice)


    }

    function updateTheLead(Modaltype='') {
        // Get buylist ID and item ID
        const buylistId = $('#selectedbuylistID').val();
        const itemId = $('#itemIdEdit').val();
        if(Modaltype == 'reject'){
            var modalMode = 'reject';
        }else{ 
            var modalMode = $('#modalMode').val();
        }

        // Build order edit data object
        const orderEditData = {
            id: itemId,  // Item ID to edit
            is_buylist: 1,  // Set as needed
            buylist_id: buylistId,
            name: $('#editOrderName').val().trim(), // Product name
            asin: $('#editOrderAsin').val().trim(), // ASIN
            buy_cost: parseFloat($('#editOrderCost').val()) || 0, // Cost to buy
            sku_total: 0, // Adjust if needed
            unit_purchased: parseInt($('#editQuantity').val()) || 0, // Number of units
            product_buyer_notes: $('#editOrderNote').val().trim(), // Buyer notes
            upc: null, // Adjust as needed
            list_price: parseFloat($('#editListPrice').val()) || 0, // List price
            min: parseFloat($('#editMinPrice').val()) || 0, // Minimum price
            max: parseFloat($('#editMaxPrice').val()) || 0, // Maximum price
            category: $('#editOrderCategory').val().trim(), // Category
            supplier: $('#editOrderSupplier').val().trim(), // Supplier
            source_url: $('#editOrderSourceUrl').val().trim(), // Source URL
            order_note: $('#editOrderProductNote').val().trim() || null, // Additional notes
            selling_price: parseFloat($('#editOrderSellingPrice').val()) || 0, // Selling price
            net_profit: parseFloat($('#editOrderNetProfit').val()) || 0, // Net profit
            bsr: $('#editOrderBsr').val().trim() || null, // Best Seller Rank
            is_hazmat: $('#editOrderIsHazmat').is(':checked') ? 1 : 0, // Hazmat
            is_disputed: $('#editOrderIsDisputed').is(':checked') ? 1 : 0, // Disputed
            msku: $('#editMsku').val().trim() || null, // MSKU
            promo: $('#editOrderPromo').val().trim() || null, // Promo details
            coupon_code: $('#editCouponCode').val().trim() || null, // Coupon code
            modalMode:modalMode,
        };
        // Send AJAX request
        $.ajax({
            url: `{{ url('item/${itemId}/update') }}`, // Ensure this is a valid URL
            type: "POST",
            data: orderEditData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            success: function(data) {
                Swal.fire('Item!', 'The item has been Updated.', 'success');
                console.log("Update successful:", data); // Log success response
                // Open the modal using Bootstrap's modal method
                loadBuylistData(buylistId)
                $('#editBuyListLeadModal').modal('hide');
                // Handle success (e.g., show a success message, refresh the list, etc.)
            },
            error: function(xhr, status, error) {
                console.error("Error updating item:", error); // Log error for debugging
                // Handle error (e.g., show an error message)
            }
        });
    }
    // Reject Button Functionality
    $(document).on('click', '.rejectItem', function () {
        const itemId = $(this).data('id'); // Get the item ID
        $('#rejectModal').modal('show');


    });
    $('#rejectSubmit').off('click').on('click', function () {
        const selectedReason = $('#rejectionReason').val(); // Get the selected value from the dropdown
        const itemId = $('#itemId').val(); // Get the selected value from the dropdown
        let reason; // Variable to hold the reason

        // Check if the selected reason is "Custom..."
        if (selectedReason === 'custom') {
            // Get the value from the textarea if the custom option is selected
            reason = $('#customReason').val().trim(); // Use .trim() to remove any leading/trailing whitespace
        } else {
            reason = selectedReason; // Use the selected reason directly
        }

        // Validate the reason
        if (reason) {
            $.ajax({
                url: `{{ url('/items/${itemId}/reject') }}`, // Use the item ID in the URL
                type: 'POST',
                data: { reason: reason },
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                success: function (response) {
                    Swal.fire('Rejected!', 'The item has been rejected.', 'success');
                    $('#rejectModal').modal('hide');
                    const buylistId = $('#selectedbuylistID').val();
                    loadBuylistData(buylistId);
                },
                error: function () {
                    Swal.fire('Error', 'There was an error rejecting the item.', 'error');
                }
            });
        } else {
            Swal.fire('Please enter a reason', '', 'warning');
        }
    });
    $(document).on('click', '.undoRejectItem', function() {
        var itemId = $(this).data('id');
        $.ajax({
            url: `{{ url('/items/${itemId}/undo-rejection') }}`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            success: function (response) {
                Swal.fire('Undo Rejection!', response.message, 'success');
                // Update UI if necessary
                const buylistId = $('#selectedbuylistID').val();
                loadBuylistData(buylistId)
            },
            error: function () {
                Swal.fire('Error', 'There was an error duplicating the item.', 'error');
            }
        });
        // Logic to undo the rejection...
    });
    // Duplicate Item Functionality
    $(document).on('click', '.duplicateItem', function () {
        const itemId = $(this).data('id');
        // Call an API or perform duplication logic here
        // Example:
        $.ajax({
            url: `{{ url('/items/${itemId}/duplicate') }}`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            success: function (response) {
                Swal.fire('Duplicated!', 'The item has been duplicated.', 'success');
                // Update UI if necessary
                const buylistId = $('#selectedbuylistID').val();
                loadBuylistData(buylistId)
            },
            error: function () {
                Swal.fire('Error', 'There was an error duplicating the item.', 'error');
            }
        });
    });
    $('#rejectedButton').on('click',function(){
        const buylistId = $('#selectedbuylistID').val();
       
        $('#backtoBtn').removeClass('d-none')
        $('#is_rejected_yes').val(1);
        loadBuylistData(buylistId,1)
        $('#rejectedButton').addClass('d-none')
        $('.buylist_active').addClass('rejected');
    })
    $('#backtoBtn').on('click',function(){
        const buylistId = $('#selectedbuylistID').val();
        $('#backtoBtn').addClass('d-none')
        $('#is_rejected_yes').val(0);
        loadBuylistData(buylistId,0)
        $('#rejectedButton').removeClass('d-none')
        $('.buylist_active').removeClass('rejected');
    })
    $(document).on('click', '.moveCopy', function () {
        const itemId = $(this).data('id');
        $('#moveCopyModal').modal('show');
        $.ajax({
            url: '{{ url("get-buylists") }}', // Endpoint to get buylists
            type: 'GET',
            success: function(response) {
                const buylistSelect = $('#selectBuylist');
                
                    buylistSelect.empty();
                    response.forEach(buylist => {
                        if($('#selectedbuylistID').val() !=buylist.id ){
                            buylistSelect.append(`<option value="${buylist.id}">${buylist.name}</option>`);
                        }
                    });
                
            },
            error: function() {
                Swal.fire('Error', 'Could not load buylists.', 'error');
            }
        });
        // Handle the form submission
        $('#submitMoveCopy').off('click').on('click', function () {
            const selectedBuylist = $('#selectBuylist').val();
            const isCopy = $('#copyLeadCheckbox').is(':checked');
            if (!selectedBuylist) {
                Swal.fire('Please select a buylist', '', 'warning');
                return;
            }
            // Send AJAX request to move or copy the item
            $.ajax({
                url: `{{ url('/items/${itemId}/move-copy') }}`,
                type: 'POST',
                data: {
                    buylist_id: selectedBuylist,
                    is_copy: isCopy ? 1 : 0
                },
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                success: function (response) {
                    Swal.fire('Success', response.message, 'success');
                    $('#moveCopyModal').modal('hide');
                    // Optionally refresh the list
                    loadBuylistData(selectedBuylist);
                },
                error: function () {
                    Swal.fire('Error', 'There was an error processing your request.', 'error');
                }
            });
        });
    });
    $(document).on('click', '.singleOrder', function() {
        const itemId = $(this).data('id'); // Get the item ID from the button's data attribute

        $.ajax({
            url: `{{ url('/items/${itemId}/create-order') }}`, // Adjust URL if needed
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Include CSRF token for Laravel
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Order Created', response.message, 'success');
                    if(response.success == true){
                        const orderId = response.order_id; // Get the order ID from the response
                        window.location.href = `/list/buycostcalculator/${orderId}`;
                    }
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'There was an error creating the order.', 'error');
            }
        });
    });
    // AJAX for Delete Multiple
    $('.dropdown-menu .deleteAllItem').on('click', function() {
        var selectedItems = $('input[name="leadCheckBox"]:checked').map(function () {
            return $(this).val();
        }).get();
        Swal.fire({
            title: 'Are you sure?',
            text: "This action will delete the item permanently!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("orders.deleteMultiple") }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    data: { ids: selectedItems },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        Swal.fire('Deleted!', 'The items has been deleted.', 'success');
                        const buylistId = $('#selectedbuylistID').val();
                        $('.checkAll').prop('checked', false);
                        $('.checkAll').removeClass('d-none')
                        $('.bulk-action-dropdown').addClass('d-none');
                        $('input[name="leadCheckBox"]:checked').prop('checked', false);
                        loadBuylistData(buylistId)
                    }
                });
            }
        })
       
    });
    $('.dropdown-menu .moveCopyToBuylist').on('click', function() {
        const itemId = $(this).data('id');
        $('#moveCopyModal').modal('show');
        $.ajax({
            url: '{{ url("get-buylists") }}', // Endpoint to get buylists
            type: 'GET',
            success: function(response) {
                const buylistSelect = $('#selectBuylist');
                
                    buylistSelect.empty();
                    response.forEach(buylist => {
                        if($('#selectedbuylistID').val() !=buylist.id ){
                            buylistSelect.append(`<option value="${buylist.id}">${buylist.name}</option>`);
                        }
                    });
                
            },
            error: function() {
                Swal.fire('Error', 'Could not load buylists.', 'error');
            }
        });
        $('#submitMoveCopy').on('click', function() {
            const buylistId = $('#selectBuylist').val();
            const copy = $('#copyLeadCheckbox').is(':checked')?1:0;
            
            if (buylistId) {
                var selectedItems = $('input[name="leadCheckBox"]:checked').map(function () {
                    return $(this).val();
                }).get();
                $.ajax({
                    url: '{{ route("orders.moveCopyToBuylist") }}',
                    method: 'POST',
                    data: { 
                        ids: selectedItems, 
                        buylist_id: buylistId, 
                        copy: copy 
                    },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        Swal.fire('Copy/Move!', response.message, 'success');
                        const buylistId = $('#selectedbuylistID').val();
                        $('.checkAll').prop('checked', false);
                        $('.checkAll').removeClass('d-none')
                        $('.bulk-action-dropdown').addClass('d-none');
                        // $('input[name="leadCheckBox"]:checked').prop('checked', false);
                        loadBuylistData(buylistId)
                        $('#buylistTable').DataTable().ajax.reload();

                        $('#moveCopyModal').modal('hide');
                    }
                });
            } else {
                alert("Please select a buylist.");
            }
        });

    });
     // AJAX for Create New Order
    $('.dropdown-menu .createOrder').on('click', function() {
        var selectedItems = $('input[name="leadCheckBox"]:checked').map(function () {
            return $(this).val();
        }).get();
        $.ajax({
            url: '{{ route("orders.createMultiple") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            data: { ids: selectedItems },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                Swal.fire('Order Created', response.message, 'success');
                if(response.success == true){
                    const orderId = response.order_id; // Get the order ID from the response
                    window.location.href = `/list/buycostcalculator/${orderId}`;
                }
            }
        });
    });
    function clearFormInputs() {
        $('#quantity').val('');
        $('#listPrice').val('');
        $('#msku').val('');
        $('#orderNote').val('');
        $('#minPrice').val('');
        $('#maxPrice').val('');
        $('#orderName').val('');
        $('#orderAsin').val('');
        $('#orderCategory').val('');
        $('#orderSupplier').val('');
        $('#orderSourceUrl').val('');
        $('#orderProductNote').val('');
        $('#orderCost').val('');
        $('#orderSellingPrice').val('');
        $('#orderBsr').val('');
        $('#orderPromo').val('');
        $('#coupon_code').val('');
        $('#orderIsHazmat').prop('checked', false);
        $('#orderIsDisputed').prop('checked', false);
    }
    function updateOrderCalculations() {
        // $('#Orderqty_cost').text(`0.00`);
        // $('#Orderqty_selling').text(`0.00`);
        // $('#Orderqty_profit').text(`0.00`);

        // let orderCost = parseFloat($("#editOrderCost").val()) || 0;
        // let orderSellingPrice = parseFloat($("#editOrderSellingPrice").val()) || 0;
        // let orderNetProfit = parseFloat($("#editOrderNetProfit").val()) || 0;
        // console.log(orderNetProfit)
        // let orderQuantity = parseInt($("#orderQuantity").val()) || 0;

        // // Calculate values and update display
        // $('#Orderqty_cost').text(`$${(orderCost * orderQuantity).toFixed(2)}`);
        // $('#Orderqty_selling').text(`$${(orderSellingPrice * orderQuantity).toFixed(2)}`);
        // $('#Orderqty_profit').text(`$${(orderNetProfit * orderQuantity).toFixed(2)}`);
        let quantity_new = parseInt($("#orderQuantity").val()) || 0;
        let cost = parseFloat($("#editOrderCost").val()) || 0;
        let sellPrice = parseFloat($("#editOrderSellingPrice").val()) || 0;
        let netProfit =  parseFloat($("#editOrderNetProfit").val()) || 0;
        appendTotalHtl(cost,quantity_new,netProfit,sellPrice)

    }
    // Attach event listeners to update calculations on input change
    $('#editQuantity, #editOrderCost, #editOrderSellingPrice, #quantity').on('input', function () {
        updateOrderCalculations();
    });
    function appendTotalHtl(cost,quantity_new,netProfit,sellPrice){
        
        let totalItemCost = cost * quantity_new;
        let totalItemSelling = sellPrice * quantity_new;
        let itemProfit = netProfit * quantity_new;
        let itemProfitPerPiece = itemProfit / (quantity_new || 1);
        // Prepare the table HTML
        let tableHtml = `
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
                                <td><strong>${quantity_new}</strong></td>
                                <td><span class="total_selling_price">$${totalItemSelling.toFixed(2)}</span></td>
                                <td><span class="total_cost_price">$${totalItemCost.toFixed(2)}</span></td>
                                <td><span class="total_gross_profit">$${itemProfit.toFixed(2)}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Per Pcs</strong></td>
                                <td><strong></strong></td>
                                <td><span class="perpcs_selling_price">$${sellPrice}</span></td>
                                <td><span class="perpcs_cost_price">$${cost.toFixed(2)}</span></td>
                                <td><span class="perpcs_gross_profit">$${itemProfitPerPiece.toFixed(2)}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>`;
        // Append or Replace inside a container
        $('.summary-box').html(tableHtml);  // ✅ Make sure you have <div id="summaryBox"></div> in your HTML
    }
    function getUrlParams() {
        const params = {};
        const searchParams = new URLSearchParams(window.location.search);
        for (const [key, value] of searchParams.entries()) {
            params[key] = value;
        }
        return params;
    }
</script>
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
