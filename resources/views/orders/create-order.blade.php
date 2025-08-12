@extends('layouts.app')

@section('title', 'Calculator')

@section('content')
<style>
    /* Keep the dropdown menu background white */
    #orderStatus:focus {
        background-color: white !important;
        color: black !important;
    }
</style>
    <div class="row d-none">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Buylists</a></li>
                        <li class="breadcrumb-item "><a href="{{ url('orders') }}">Order</a></li>
                        <li class="breadcrumb-item active"><a href="#">Calculator</a></li>
                        <!--<li class="breadcrumb-item "><a href="{{ url('shippingbatches') }}">Shipping</a></li>-->
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <input type="hidden" name="order_id" id="order_id" value="{{ $order->id }}">
    <!-- Row with Form Elements and Attachments -->
    <div class="row align-items-stretch justify-content-between">
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
            <input id="source" name="source" class="form-control"  value="{{$parse }}" >
            <div class="col-md-1 mt-1">
                
            </div>
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
            {{-- <input id="email" name="email" value="{{ $order->email }}" class="form-control"> --}}
          </div>
        </form>
      </div>
      <!-- Middle (Attachments Card) -->
      <div class="col-md-5 d-flex justify-content-end">
        <div class="card attachments-card w-100 h-100">
          <div class="card-header d-flex justify-content-between py-2">
            <label for="orderattachments" class="col-form-label">Attachments</label>
            <i class="fa fa-eye-slash" style="cursor: pointer;"></i>
          </div>
          <div class="card-body py-2 px-0" style="cursor: pointer;">
            <div class="order-attachments">
              <div class="row justify-content-start align-items-center"  data-bs-toggle="modal" data-bs-target="#addAttachmentModal" style="cursor: pointer;">
                <div class="col-auto text-center">
                  <i class=" ri-add-circle-fill fa-lg text-primary" style="padding-left: 23px;font-size: 20px"></i>
                </div>
                <div class="col-auto">
                  <span class="my-auto">Add New File</span>
                </div>
              </div>
              <div id="fileCards">

              </div>
              
            </div>
          </div>
        </div>
      </div>
      <!-- Skip and Reset Buttons -->
      <div class="col-md-1 d-flex justify-content-end align-items-start">
        <button class="btn btn-primary mx-1" onclick="saveChanges()">Skip</button>
        <a href="{{ url()->previous() }}" class="btn btn-danger mx-1" id="resetAll">Cancel</a>
        <button class=" btn btn-success " type="button" id="saveButton" onclick="saveChanges()">Save</button>
      </div>
    </div>    
    <hr class="mt-3">
    <!-- Calculator Section -->
    <div class="row">
      <div class="col-md-9">
        <div class="row justify-content-between">
          <div class="col-md-12 col-lg-12 col-xl-12">
              <div class="card mb-3">
                  <div class="card-header d-flex justify-content-between align-items-center">
                      <label class="col-form-label m-0">Actual Cost Per Unit Calculator</label>
                      <div class="d-flex align-items-center">
                          <label for="buyCostCalcType" class="text-muted me-2 mb-0">Calculator Type:
                              <i data-v-28b80e2c="" aria-hidden="true" class="ri-question-fill" data-original-title="" title=""></i>
                          </label>
                          <select id="buy_cost_type" name="buy_cost_type" class="form-select form-select-sm text-center">
                              <option value="individual" {{ $order->buy_cost_type == 'individual'?'selected':'' }}>Single Unit Buy Cost</option>
                              <option value="sku" {{ $order->buy_cost_type == 'sku'?'selected':'' }} selected>Total Buy Cost</option>
                          </select>
                      </div>
                  </div>
                  
                <div class="card-body p-0">
                    <!-- Table header with aligned columns -->
                    <table class="table text-center table-bordered">
                        <thead>
                        <tr>
                            <th>ASIN</th>
                            <th>Name</th>
                            <th># of Units</th>
                            <th class="single_cost d-none">Single Unit Buy Cost <i aria-hidden="true" class="ri-question-fill"></i></th>
                            <th class="total_cost">Total Buy Cost <i aria-hidden="true" class="ri-question-fill"></i></th>
                            <th>Calculated Buy Cost</th>
                        </tr>
                        </thead>
                        <tbody id="orderInputsContainer"></tbody>
                        <tbody id="savedItemsContainer"></tbody>
                    </table>

                    <div class="text-center mt-3 mb-2">
                        <button type="button" class="btn btn-outline-primary btn-lg" id="addNewOrderItemBtn">
                        <i class="fa fa-plus-circle fa-lg me-2"></i> Add New Order Item
                        </button>
                    </div>
                </div>

              </div>
          </div>
        </div>
        <div class="row mb-2">
          <div class="col-md-4">
            <div class="card mb-5 h-100">
                <div class="card-header py-1">
                    <label class="col-form-label">Order Details</label>
                </div>
                <div class="card-body pb-0 pt-2 mb-4">
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
          <div class="col-md-4">
            <div class="card mb-3 h-100">
                <div class="card-header py-1">
                    <label class="col-form-label">Payment Source</label>
                </div>
                <div class="card-body pb-0 pt-2">
                    <form>
                        <div class="row mb-1">
                            <label for="buyCostCalcCardUsed" class="col-md-6">Card Used</label>
                            <div class="col-md-6 text-center">
                                <input type="text" value="{{ $order->card_used }}" class="form-control" name="card_used" id="card_used">
                            </div>
                        </div>
                        <div class="row mb-1">
                            <label for="buyCostCalcCashBackSource" class="col-md-6  d-flex justify-content-between">Cash Back Source
                            <!-- Add New Cashback Source Button -->
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
                                
                              {{-- <input type="text" class="form-control" name="cash_back_source" id="cash_back_source" value="{{ $order->cash_back_source }}"> --}}
                            </div>
                        </div>
                        <div class="row">
                            <label for="buyCostCalcCashBackPercentage" class="col-md-6">Cash Back Percentage</label>
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
          </div>
        
          <div class="col-md-4">
            <div class="card mb-3 h-100">
                <div class="card-header py-1 d-flex justify-content-between">
                    <label for="buyCostCalcOrderNote" class="col-form-label">Order Notes</label>
                    <i class="ri-edit-box-fill edit-note" style="cursor: pointer;"></i>
                </div>
                <div class="card-body pb-0 pt-2" id="noteCard-body">
                    <div class="order-notes">
                        <p id="noteTextarea">{{ $order->note }}</p>
                        <textarea name="" class="form-control d-none" id="noteTextarea" name="noteTextarea" cols="30" rows="3">{{ $order->note }}</textarea>
                    </div>
                </div>
            </div>
          </div>
        
        </div>
      </div>
        <div class="col-md-3 col-lg-3 col-xl-3">
            <div class="row">
                <div class="col-md-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <label class="col-form-label">Order Totals</label>
                    </div>
                    <div class="card-body pb-0">
                        <form>
                            <div class="form-group row">
                                <label for="buyCostCalcPreTaxDiscount" class="col-md-6">Pre-Tax Discount <i class="fa fa-question-circle-o pt-2" aria-hidden="true"></i></label>
                                <div class="col-md-6 calc-input left-inner-addon">
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" min="0" step="0.01" value="{{ number_format($order->pre_tax_discount, 2, '.', '') }}" class="form-control text-center" placeholder="" id="pre_tax_discount" name="pre_tax_discount" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="buyCostCalcPostTaxDiscount" class="col-md-6">Post-Tax Discount <i class="fa fa-question-circle-o pt-2" aria-hidden="true"></i></label>
                                <div class="col-md-6 calc-input left-inner-addon">
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" min="0" step="0.01" class="form-control text-center" placeholder="" value="{{ number_format($order->post_tax_discount, 2, '.', '') }}" name="post_tax_discount" id="post_tax_discount" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="buyCostCalcShippingCost" class="col-md-6">Shipping Cost</label>
                                <div class="col-md-6 calc-input left-inner-addon">
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" min="0" step="0.01" class="form-control text-center" placeholder="" value="{{ number_format($order->shipping_cost, 2, '.', '') }}" id="shipping_cost" name="shipping_cost" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="buyCostCalcSalesTax" class="col-md-6">Sales Tax</label>
                                <div class="col-md-6 calc-input left-inner-addon">
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" min="0" step="0.01" class="form-control text-center" name="sales_tax" id="sales_tax" value="{{ number_format($order->sales_tax, 2, '.', '') }}" placeholder="Single Unit Buy Cost" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <div class="col-9">
                                    <label class="form-check-label" for="is_sale_tax">Sales Tax paid on Shipping Cost</label>
                                </div>
                                <div class="col-3 text-end">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_sale_tax" name="is_sale_tax" checked="">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer bg-calculated pb-0">
                        <form>
                            <div class="form-group row">
                                <label for="buyCostCalcSalesTaxPercentage" class="col-md-6">Sales Tax Rate</label>
                                <div id="buyCostCalcSalesTaxPercentage" class="col-md-6 text-center p-0">
                                    NaN%
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="buyCostCalcPreTaxSubtotal" class="col-md-6">Subtotal (pre-tax)</label>
                                <div id="buyCostCalcPreTaxSubtotal" class="col-md-6 text-center p-0">
                                    $0.00
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="buyCostCalcGrandTotal" class="col-md-6">
                                    <h5>Grand Total</h5>
                                </label>
                                <div id="buyCostCalcGrandTotal" class="col-md-6 text-center p-0">
                                    <h5>$0.00</h5>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
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
    @include('modals.add-file-modal')
    @include('modals.view-file-modal')
    @include('modals.edit-file-modal')

@endsection
@section('script')
@include('orders.js.createjs')  
@endsection