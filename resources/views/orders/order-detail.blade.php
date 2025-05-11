@extends('layouts.app')

@section('title', 'Orders')

@section('content')
    <style>
        .orderSection input,
        .orderSection div,
        .orderSection span,
        .orderSection select {
            font-size: 14px !important;
        }
        /* Add some margin to the top of the icon to align it with the label */
        .edit-note {
            margin-top: 4px;
        }

        /* Reduce padding inside the textarea */
        #noteTextarea {
            padding: 8px;
        }
        #cash_back_percentage_display {
            display: inline-block;
            border: 1px solid #ced4da; /* Matches the default Bootstrap input border */
            border-radius: 0.375rem;  /* Matches Bootstrap input border radius */
            padding: 0.375rem 0.75rem; /* Matches Bootstrap input padding */
            width: 100%; /* Make it stretch like the input field */
            box-sizing: border-box; /* Include padding and border in width */
            background-color: #fff; /* Matches the input field's background */
            cursor: pointer; /* Indicate it's clickable */
        }
        /* Keep the dropdown menu background white */
        #orderStatus:focus {
            background-color: white !important;
            color: black !important;
        }

    </style>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Buylists</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('orders') }}">Order</a></li>
                       {{-- <li class="breadcrumb-item "><a href="/order/{{ $order->id  }}" target="_blank">{{ $order->id  }}</a></li> --}}
                        <!--<li class="breadcrumb-item "><a href="{{ url('shippingbatches') }}">Shipping</a></li>-->
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <input type="hidden" name="order_id" id="order_id" value="{{ $order->id }}">
    <!-- Row with Form Elements and Attachments -->
    <div class="row align-items-stretch justify-content-between">
        <div class="col-md-6">
        @if(auth()->user()->role_id ==3) 
            <div class="form-group mb-2">
                <label for="order_id">Created By:</label>
                <input id="created_by" name="created_by" class="form-control" autocomplete="off" value="{{ $order->createdBy->name }}" readonly>
            </div>
        @else
        <div class="form-group mb-2">
            <label for="order_id">Buyer:</label>
            <select class="form-select select2" name="buyer_id" id="buyer_id">
                <option value="">Select Buyer</option>
                @foreach ($buyers as $buyer)
                    <option value="{{ $buyer->id }}" {{ $order->buyer_id == $buyer->id?'selected':'' }}>{{ $buyer->name }}</option>                    
                @endforeach
            </select>
        </div>
        @endif
        </div>
        <div class="col-md-6">
            <button class="btn btn-success float-end" type="button" id="saveButton" onclick="saveChanges()">Save</button>
        </div>
      <!-- Left Side (Order ID and Source Fields) -->
      <div class="col-md-2 d-flex">
        <form class="w-100">
          <div class="form-group mb-2">
            <label for="order_id">Order ID:</label>
            <input id="order_id" name="order_id" class="form-control" autocomplete="off" value="{{ $order->order_id }}">
          </div>
          <div class="form-group mb-2">
            <label for="source">Source:</label>
            @php   
            $parse ='';
            if( $order->source != null){
                $parse = parse_url($order->source);
                if(isset($parse['host'])){
                        $parse = $parse['host'];
                }else{
                        $parse = $order->source;
                }
            }
               
                // return $parse['scheme'] . '://' . $parse['host'];
            @endphp
            <input id="source" name="source" class="form-control"  value="{{ $parse }}" >
            
          </div>
        </form>
      </div>
      <!-- Right Side (Destination and Email Fields) -->
      <div class="col-md-3 d-flex">
        <form class="w-100">
          <div class="form-group mb-2">
            <label for="destination">Destination:</label>
            <select class="form-control" name="destination" id="destination">                
                <option value="" selected disabled>Select Destination</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->location }}" {{ $location->location == $order->destination?'selected':'' }}>{{ $location->location }}</option>
                @endforeach
            </select>
            {{-- <input id="destination" name="destination" value="{{ $order->destination }}" class="form-control" autocomplete="off"> --}}
          </div>
          <div class="form-group mb-2">
            <div class="d-flex justify-content-between align-items-center">
                <label for="email">Email Used:</label>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addEmailModal">
                    <i class="ri-add-line"></i> Add Email
                </button>
            </div>
            <select class="form-control" name="email" id="email">
                <option value="" selected disabled>Select Email</option>
                @foreach ($emails as $email)
                    <option value="{{ $email->email }}" {{ $order->email == $email->email?'selected':'' }}>{{ $email->email }}</option>
                @endforeach
            </select>
            {{-- <input id="email" name="email" value="" class="form-control"> --}}
          </div>
        </form>
      </div>
      <!-- Middle (Attachments Card) -->
      <div class="col-md-6 d-flex justify-content-end">
        <div class="card attachments-card w-100 h-100">
            <div class="card-header d-flex justify-content-between py-2">
                <label for="orderattachments" class="col-form-label">Attachments</label>
                <!-- Eye icon for toggle -->
                <i id="toggleCardBody" class="ri-eye-off-fill" style="cursor: pointer;"></i>
            </div>
            <div class="card-body py-2 px-0" style="cursor: pointer; display: block;">
                <div class="order-attachments">
                    <div class="row justify-content-start align-items-center" data-bs-toggle="modal" data-bs-target="#addAttachmentModal" style="cursor: pointer;">
                        <div class="col-auto text-center">
                            <i class="ri-add-circle-fill fa-lg text-primary" style="padding-left: 23px; font-size: 20px"></i>
                        </div>
                        <div class="col-auto">
                            <span class="my-auto">Add New File</span>
                        </div>
                    </div>
                    <div id="fileCards">
                        <!-- Dynamic file cards will be appended here -->
                    </div>
                </div>
            </div>
        </div>
    </div>    
      <!-- Skip and Reset Buttons -->
      <div class="col-md-1 d-none d-flex justify-content-end align-items-start">
        <button class="btn btn-primary mx-1">Skip</button>
        <button class="btn btn-danger mx-1">Reset</button>
      </div>
    </div>    
    <hr class="mt-3">
    <!-- Calculator Section -->
    <div class="row">
        <div class="col-md-3 col-lg-3 col-xl-3">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-3">
                        <div class="card-header py-1">
                            <label class="col-form-label">Order Totals</label>
                        </div>
                        <div class="card-body pb-0 pt-2 mb-2 orderSection ">
                            <form>
                                <div class="row mb-1">
                                    <label for="orderViewPreTaxSubtotal" class="col-md-6 col-form-label">Subtotal (pre-tax)</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input id="orderViewPreTaxSubtotal" name="subtotal" type="number" step="0.1" class="form-control form-control-sm text-center" value="{{ number_format($order->subtotal,2)}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <label for="orderViewShippingTotal" class="col-md-6 col-form-label">Shipping Total</label>
                                    
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input name="shipping_cost" id="orderViewShippingTotal" type="number" step="0.1" class="form-control form-control-sm text-center"value="{{ number_format($order->shipping_cost,2) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <label for="orderViewSalesTax" class="col-md-6 col-form-label">Sales Tax Paid</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input id="orderViewSalesTax" name="sales_tax" type="number" min="0" step="0.01" class="form-control form-control-sm text-center" value="{{ number_format($order->sales_tax,2) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <label for="orderViewSalesTaxRate" class="col-md-6 col-form-label">Sales Tax Rate</label>
                                    <div class="col-md-6 text-center">
                                        <!-- Display text -->
                                        <div class="input-group">
                                            <span class="input-group-text" id="sales_tax_rate_span">%</span>
                                            <span id="sales_tax_rate"  class="editable-sale-text form-control">
                                                {{ isset($order->sales_tax_rate) ? number_format($order->sales_tax_rate,2) . '%' : '0.00%' }}
                                            </span>
                                        </div>
                                        <!-- Input field (hidden by default) -->
                                        <div class="input-group">
                                            <span class="input-group-text d-none" id="spanofpercentage">%</span>
                                            <input type="number" step="0.1"  value="{{ number_format($order->sales_tax_rate,2) }}" placeholder="0.00%"  class="form-control d-none text-center" name="sales_tax_rate" id="orderViewSalesTaxRate">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label for="orderViewGrandTotal" class="col-md-6 col-form-label">Grand Total</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            @php
                                               $total = 0;
                                               $total += $order->subtotal;
                                               $total += $order->shipping_cost;
                                               $total += $order->sales_tax;
                                            @endphp
                                            <span class="input-group-text">$</span>
                                            <input name="total" id="orderViewGrandTotal" type="number" step="0.1" class="form-control form-control-sm text-center"value="{{ number_format($total,2) }}">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header py-1">
                            <label class="col-form-label">O-R-S (E)</label>
                        </div>
                        <div class="card-body py-0 mb-5 orderSection">
                            <div class="row py-0">
                                <div class="col-12-md">
                                    <div class="row mt-2">
                                        <div class="col-md-6 d-flex justify-content-between">
                                            <strong>Ordered:</strong>
                                            @if($order->total_units_purchased != 0)
                                                <span class="badge bg-dark me-2" id="totalItemOrders">{{ $order->total_units_purchased }}</span>
                                            @else
                                                <span id="totalItemOrders" class="me-2">-</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6 d-flex justify-content-between">
                                            <strong>Received:</strong>
                                            @if($order->total_units_received != 0)
                                                <span class="badge bg-info me-2" id="ordertotalReceiced">{{ $order->total_units_received }}</span>
                                            @else
                                                <span id="ordertotalReceiced" class="me-2">-</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6 d-flex justify-content-between">
                                            <strong>Shipped:</strong>
                                            @if($order->total_units_shipped != 0)
                                                <span class="badge bg-success me-2" id="orderTotalShipped">{{ $order->total_units_shipped }}</span>
                                            @else
                                                <span id="orderTotalShipped" class="me-2">-</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6 d-flex justify-content-between">
                                            <strong>Errors:</strong>
                                            @if($order->unit_errors != 0)
                                                <span class="badge bg-danger me-2" id="orderTotalError">{{ $order->unit_errors }}</span>
                                            @else
                                                <span id="orderTotalError" class="me-2">-</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="row mb-2">
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header py-1">
                        <label class="col-form-label">Payment Source</label>
                    </div>
                    <div class="card-body pb-0 pt-2 orderSection">
                        <form class="mb-3">
                            <div class="row mb-1">
                                <label for="buyCostCalcCardUsed" class="col-md-6">Card Used</label>
                                <div class="col-md-6 text-center">
                                    <input type="text" value="{{ $order->card_used }}" class="form-control text-center" name="card_used" id="card_used">
                                </div>
                            </div>
                            <div class="row mb-1">
                                <label for="buyCostCalcCashBackSource" class="col-md-6">Cash Back Source
                                    <span type="button" class="ms-2" data-bs-toggle="modal" data-bs-target="#addCashbackSourceModal">
                                        +
                                    </span>
                                </label>
                                <div class="col-md-6 text-center"  >
                                <select name="cash_back_source" id="cash_back_source" class="form-select">
                                    <option value="" disabled>Choose Option</option>
                                    @foreach ($cashback as $item)
                                    <option value="{{ $item->name }}" {{ $item->nam ==  $order->cash_back_source?'selected':'' }}>{{ $item->name }}</option>
                                        
                                    @endforeach
                                </select>
                                {{-- <input type="text" class="form-control text-center" value="{{ $order->cash_back_source}}" name="cash_back_source" id="cash_back_source"> --}}
                                </div>
                            </div>
                            <div class="row">
                                <label for="cash_back_percentage" class="col-md-6">Cash Back Percentage</label>
                                <div class="col-md-6 text-center">
                                    <!-- Display text -->
                                    <span 
                                        id="cash_back_percentage_display" 
                                        class="editable-text form-control"
                                    >
                                    {{ isset($order->cash_back_percentage) ? number_format($order->cash_back_percentage,2) . '%' : '0.00%' }}

                                    </span>
                            
                                    <!-- Input field (hidden by default) -->
                                    <input 
                                        type="number" 
                                        step="0.1"  
                                        value="{{ $order->cash_back_percentage }}" 
                                        placeholder="0.00%" 
                                        class="form-control d-none text-center" 
                                        name="cash_back_percentage" 
                                        id="cash_back_percentage_input"
                                    >
                                </div>
                            </div>
                            
                        </form>
                    </div>
                </div>
                <div class="card mb-5">
                    <div class="card-header py-1">
                        <label class="col-form-label">Order Details</label>
                    </div>
                    <div class="card-body pb-0 pt-2 mb-4 orderSection">
                        <form>
                            <div class="row mb-1">
                                <label for="buyCostCalcCreatedAt" class="col-md-6 mb-1">Created</label>
                                <div id="buyCostCalcCreatedAt" class="col-md-6 text-center">
                                    {{ \Carbon\Carbon::parse($order->created_at)->format('M jS, Y') }}
                                </div>
                            </div>
                            <div class="row mb-1">
                                <label for="buyCostCalcDateOrdered" class="col-md-6">Date Ordered</label>
                                <div class="col-md-6">
                                    <input id="date" name="date" type="date" class="form-control form-control-sm text-center" 
                                           value="{{ \Carbon\Carbon::parse($order->date)->format('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="row mb-1">
                                <label for="buyCostCalcUpdatedAt" class="col-md-6 mb-1">Last Updated</label>
                                <div id="buyCostCalcUpdatedAt" class="col-md-6 text-center">
                                    {{ \Carbon\Carbon::parse($order->updated_at)->format('M jS, Y') }}
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="status" class="col-md-6">Order Status <i class="fa fa-info-circle" aria-hidden="true"></i></label>
                                <div class="col-md-6 text-center">
                                    <select id="orderStatus" name="status" class="form-select form-select-sm w-100" onchange="changeBackgroundColor()">
                                        <option value="draft" {{ $order->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="ordered" {{ $order->status === 'ordered' ? 'selected' : '' }}>Ordered</option>
                                        <option value="partially received" {{ $order->status === 'partially received' ? 'selected' : '' }}>Partially Received</option>
                                        <option value="received in full" {{ $order->status === 'received in full' ? 'selected' : '' }}>Received in Full</option>
                                        <option value="reconcile" {{ $order->status === 'reconcile' ? 'selected' : '' }}>Reconcile</option>
                                        <option value="closed" {{ $order->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                        <option value="canceled" {{ $order->status === 'canceled' ? 'selected' : '' }}>Canceled</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
            </div>
            
            <div class="col-md-8">
                <div class="card mb-3 d-none" id="eventCard" style="max-height: 400px;">
                    <div class="card-header py-1 d-flex justify-content-between align-items-center">
                        <label for="buyCostCalcOrderNote" class="col-form-label">Event Log</label>
                    </div>
                    <div class="card-body pb-0 pt-2" style="overflow-y: auto; max-height: 300px;">
                        <div class="table-responsive">
                            <table class="table table-hover" id="eventsTable">
                                <thead>
                                    <tr>
                                        <th>Event Type</th>
                                        <th>Asin</th>
                                        <th>Qty</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Table rows will be appended dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                                          
                <div class="card mb-3">
                    <div class="card-header py-1 d-flex justify-content-between align-items-center">
                        <label for="buyCostCalcOrderNote" class="col-form-label">Order Notes</label>
                        <!-- Single click on the icon to toggle edit mode -->
                        <i class="ri-edit-box-fill edit-note" style="cursor: pointer;" onclick="toggleEditNote()"></i>
                    </div>
                    <div class="card-body pb-0 pt-2" id="noteCard-body" style="cursor: pointer">
                        <div class="order-notes mb-5" >
                            <!-- Double-click on the paragraph to edit -->
                            <p id="noteText" class="note-text">{{ $order->note }}</p>
                            <textarea class="form-control d-none" id="noteTextarea" name="note" cols="30" rows="5">{{ $order->note }}</textarea>
                        </div>
                    </div>
                </div>        
                <div class="card mb-3" id="appendDataProfit">
                    
                </div>                    
            </div>
            
            </div>
        </div>
    </div>
    <div class="row mb-5">
        <div class="col-md-12">
            <h3>Products <span id="product-count">(0)</span> <button class="btn btn-primary btn-sm" onclick="addItem()">Add Item</button></h3>
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="product-table-body">
                <!-- Rows will be appended here dynamically -->
            </tbody>
        </table>
        </div>
    </div>
    <!-- Modal Structure -->
    <div class="modal fade" id="addEmailModal" tabindex="-1" aria-labelledby="addEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEmailModalLabel">Add New Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addEmailForm">
                        <div class="mb-3">
                            <label for="new-email" class="form-label">New Email</label>
                            <input type="email" class="form-control" id="new-email" name="new_email" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Email</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for Adding Cashback Source -->
    <div class="modal fade" id="addCashbackSourceModal" tabindex="-1" aria-labelledby="addCashbackSourceLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCashbackSourceLabel">Add Cashback Source</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCashbackSourceForm">
                        <div class="mb-3">
                            <label for="cashback-source-name" class="form-label">Cashback Source Name</label>
                            <input type="text" class="form-control" id="cashback-source-name" name="name" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Cashback Source</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Work Order Modal -->
    <div class="modal fade" id="workOrderModal" tabindex="-1" role="dialog" aria-labelledby="workOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            
                <div class="modal-header">
                <h5 class="modal-title" id="workOrderModalLabel">Select Work Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                
                <div class="modal-body">
                    <input type="hidden" name="lineItemId" id="lineItemId">
                <div class="form-group">
                    <label for="workOrderSelect">Choose a Work Order</label>
                    <select class="form-control" id="workOrderSelect">
                        <option value="">-- Select --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="workOrderSelect">Quantity</label>
                    <input class="form-control" type="number" name="productQtyInput" id="productQtyInput" value="1" step="1">
                </div>
                </div>
        
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Close</button>
                <button type="button" class="btn btn-primary" id="assignWorkOrderBtn">Send</button>
                </div>
        
            </div>
        </div>
    </div>
    @include('modals.add-file-modal')
    @include('modals.view-file-modal')
    @include('modals.edit-file-modal')
    @include('modals.create-order-modal')
    @include('modals.edit-detail-page-item')
    @include('modals.add-shipping-modal')
    @include('modals.edit-shipping-event')
    @include('modals.edit-issue-events')
@endsection
@include('orders.js.order-detailjs')
