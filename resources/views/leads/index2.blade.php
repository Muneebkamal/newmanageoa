@extends('layouts.app')

@section('title', 'Leads')

@section('content')
<style>
    .itemcard.active {
        border: 2px solid #007bff; /* Blue border for active state */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Optional shadow for emphasis */
    }
    #leads-table-view {
        width: 100% !important;
        table-layout: auto;
    }
    /* Move the information text to the top center */
    /* Move the information text to the top center */
.dataTables_info {
    position: absolute;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10;
}

/* Fix pagination to be at the bottom center */
.dataTables_paginate {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10;
}
/* Align the search input to the left */
.dataTables_filter {
    text-align: left !important;
}
.dataTables_filter label {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Ensure the per-page dropdown remains visible */
.dataTables_length {
    position: relative; /* Remove absolute positioning */
    z-index: 10; /* Ensure it stays above other elements */
    margin-bottom: 40px; /* Adjust margin if necessary */
}

/* Remove unnecessary absolute positioning that might hide other controls */
table.dataTable {
    position: relative; /* Make sure table is not absolutely positioned */
    margin-top: 50px; /* Adjust the table margin if needed */
}



</style>
    @include('modals.tags.tag-modal')
    @include('modals.add-lead-buylist')
    @include('modals.tags.select-tag')
    @include('modals.add-bundle-buylist')
    @include('modals.add-bundle-create-order')

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                       <li class="breadcrumb-item active d-flex align-items-center">
                            <a href="{{ url('leads-new') }}"><h3 class="mb-0 me-3">Leads New</h3></a>
                            <a href="{{ url('leads') }}" class="btn btn-sm btn-outline-primary">View Old Leads</a>
                        </li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <div class="row">
        <input type="hidden" name="end_date" id="end_date">
        <input type="hidden" name="start_date" id="start_date">
        <div class="col-md-4"></div>
        <div class="col-md-6 mb-3">
            <div class="filterClass card card-body py-2 px-3 mb-0 w-100" style="margin-top: -10px;">
                <div class="row justify-content-center">
                    <div class="col-auto">
                        <h6 class="text-center my-1">Totals: <span id="totals"></span></h6>
                    </div>
                    <div class="w-100 d-xl-none d-sm-none"></div>
                    <div class="col-auto">
                        <h6 class="card-subtitle my-1 text-center">
                            <span class="text-muted">SKUs</span>: <span id="totalSku">0</span>
                        </h6>
                    </div>
                    <div class="col-auto">
                        <h6 class="card-subtitle my-1 text-center">
                            <span class="text-muted">Cost</span>: <span id="totalCost">$0.00</span>
                        </h6>
                    </div>
                    <div class="col-auto">
                        <h6 class="card-subtitle my-1 text-center">
                            <span class="text-muted">Units</span>: <span id="Totalunits">0</span>
                        </h6>
                    </div>
                </div>
            </div>
            <!-- Collapse Example -->
        </div>
        
        <div class="col-md-12">
            <div class="row filterClass">
                <!-- Date Filter -->
                <div class="col-md-3 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text font-weight-bold">Date</span>
                        </div>
                        {{-- <select id="inputDateOption" class="form-control custom-select-sm">
                            <option value="0">Today</option>
                            <option value="7" >Last 7 days</option>
                            <option value="30" >Last 30 days</option>
                            <option value="90" selected>Last 90 days</option>
                            <option value="custom">Custom Range</option>
                            <option value="827">All</option>
                        </select> --}}
                        <input type="text" id="dateRangePicker" class="form-control custom-select-sm" />
                    </div>
                </div>
                <!-- Sort By -->
                <div class="col-md-3 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text font-weight-bold">Sort By</span>
                        </div>
                        <select id="inputSortSelect" class="form-control custom-select-sm">
                            <option >Choose...</option>
                            <option value="created_at" selected="selected">Date</option>
                            <option value="cost">Buy Cost</option>
                            <option value="category">Category</option>
                            <option value="sell_price">Low FBA</option>
                            <option value="net_profit">Net Profit</option>
                            <option value="bsr">90 Day Avg</option>
                            <option value="roi">ROI</option>
                            <option value="supplier">Supplier</option>
                            <option value="asin">ASIN</option>
                            <option value="promo">Promo</option>
                            <option value="couponan">Coupon Code</option>
                        </select>
                        <select id="orderbyinput" class="form-control custom-select-sm">
                            <option value="asc">Oldest to Newest</option>
                            <option value="desc" selected>Newest to Oldest</option>
                        </select>
                    </div>
                </div>
                <!-- ROI Filter -->
                <div class="col-md-3 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text font-weight-bold">ROI (%)</span>
                        </div>
                        <input type="number" id="roiInputMin" step="1" class="form-control">
                        <input type="number" id="roiInputMax" step="1" class="form-control">
                    </div>
                </div>
                <!-- Net Profit -->
                <div class="col-md-3 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text font-weight-bold">Net Profit ($)</span>
                        </div>
                        <input type="number" id="netProfitInputMin" class="form-control">
                        <input type="number" id="netProfitInputMax" class="form-control">
                    </div>
                </div>
                <!-- Low FBA -->
                <div class="col-md-3 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text font-weight-bold">Low FBA ($)</span>
                        </div>
                        <input type="number" id="lowFBAInputMin" class="form-control">
                        <input type="number" id="lowFBAInputMax" class="form-control">
                    </div>
                </div>
                <!-- 90 Day Avg -->
                <div class="col-md-3 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text font-weight-bold">90 Day Avg</span>
                        </div>
                        <input type="number" id="ninetyDayAvgInputMin" class="form-control">
                        <input type="number" id="ninetyDayAvgInputMax" class="form-control">
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="input-group input-group-sm mt-1">
                       <button class="btn btn-primary" id="apply-filter">Apply Filter</button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-10">
                    Lead for This <span id="filterTitle">Last 90 days</span>: <span id="leadsCount"></span>
                </div>
                <div class="col-md-2 position-relative">
                    <div class="hstack gap-2 flex-wrap float-end">
                        <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="true" aria-controls="collapseExample">
                            <i class="ri-settings-2-fill"></i>
                        </a>
                    </div>
                    <div class="collapse mt-5 mx-2" id="collapseExample" style="z-index: 1050; position: absolute; background: #fff; width: 80%;">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-check-label" for="compactView">Compact View</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="compactView" >
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-check-label" for="hideFilters">Hide Filters</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="hideFilters" >
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-check-label" for="showLatestStats">Show Latest Stats</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="showLatestStats" >
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-check-label" for="latestStats">Show Filter By Latest Stats</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="latestStats" >
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-check-label" for="excludeHazmat">Show Exclude Hazmat</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="excludeHazmat" >
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-check-label" for="excludeDisputed">Show Exclude Disputed</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="excludeDisputed" >
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
    </div>
    
    <div class="row mt-3">
        <div id="tags-alert"></div>
        <div class="col-md-12" id="cardView">
            <table id="leads-table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>
                            {{-- <div class="col-md-12 d-flex justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between ms-4">
                                        <input type="checkbox" class="me-2" name="checkAllLead" id="checkAllLead">
                                        <select id="dropdownApplyTagsToSelected" class="form-control custom-select-sm  ms-3" style="width:200px" disabled>
                                            <option value="" selected disabled>Bulk Apply tags</option>
                                        </select>
                                        
                                    </div>
                                </div>
                                <div>
                                    <button type="button" id="" class="btn btn-outline-secondary dropdown-toggle me-2" data-bs-toggle="dropdown" 
                                        aria-haspopup="true" aria-expanded="true">Select Lists
                                    </button>
                                    <div class="dropdown-menu" data-popper-placement="top-start"
                                        style="position: absolute; inset: auto auto 0px 0px; margin: 0px; transform: translate(0px, -40px);">
                                        <div class="mt-2" style="overflow-y: auto; max-height: 25vh;">
                                            <h6 class="dropdown-header">My Lists</h6>
                                            <ul id="sorucesUl">
                                                <!-- Dynamic content will be appended here -->
                                            </ul>
                                        </div>
                                        <a class="dropdown-item" id="selectToggleButton">
                                            
                                        </a>
                                    </div>


                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle me-2" data-bs-toggle="dropdown"  aria-haspopup="true"
                                        aria-expanded="true">Select List Type
                                    </button>
                                    <div class="dropdown-menu" data-popper-placement="top-start"
                                        style="position: absolute; inset: auto auto 0px 0px; margin: 0px; transform: translate(0px, -40px);">
                                        <div id="" class="mt-2" style="overflow-y: auto; max-height: 25vh;">
                                            <form class="p-2">
                                                <div  class="form-check dropdown-item">
                                                    <input  type="checkbox" value="Top Shelf Leads" id="topShelfCheckbox" data-gtm-form-interact-field-id="0"> <label  for="topShelfCheckbox">Top Shelf Leads</label>
                                                </div> 
                                                <div  class="form-check  dropdown-item">
                                                    <input  type="checkbox" value="Honorable Mentions" id="honorableCheckbox" data-gtm-form-interact-field-id="1"> <label  for="honorableCheckbox">Honorable Mentions</label>
                                                </div> 
                                                <div  class="form-check dropdown-item">
                                                    <input  type="checkbox" value="Replenishables" id="replenCheckbox"> <label  for="replenCheckbox">Replenishables</label>
                                                </div> 
                                                <div  class="form-check dropdown-item">
                                                    <input  type="checkbox" value="Only Bundles" id="onlyBundlesCheckbox"> <label  for="onlyBundlesCheckbox">Only Show Bundles</label>
                                                </div>
                                            </form>
                        
                                        </div>
                                       
                                    </div>
                                    
                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle me-2" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="true" onclick="tagsGet()">Select Tags
                                    </button>
                                    <div class="dropdown-menu" data-popper-placement="top-start"
                                        style="position: absolute; inset: auto auto 0px 0px; margin: 0px; transform: translate(0px, -40px);">
                                        <div class="mx-1 form-icon right">
                                            <input type="text" class="form-control form-control-icon" id="iconrightInput" placeholder="Search">
                                            <i class="ri-search-line"></i>
                                        </div>
                                        <div id="tags_get" class="mt-2" style="overflow-y: auto; max-height: 25vh;">
                        
                                        </div>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#" onclick="unCheckTags(); return false;"> <i
                                                class="ri-close-line text-primary me-2"></i> Deselect All
                                            Tags</a>
                                        <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#manage-tag-modal"
                                            onclick="tagsList(); return false;">
                                            <i class="ri-pencil-line text-primary me-2"></i> Manage Tags
                                        </a>
                                    </div>
                                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal"
                                    data-bs-target="#exampleModalScrollable" onclick="formLeadClear()">Add Lead</button>
                                    <div class="input-group input-group-sm">
                                        <div  class="input-group-prepend">
                                            <label  for="perPageSelect" class="input-group-text">Page Size:</label>
                                        </div>
                                        <select  id="perPageSelect" class="select2">
                                            <option  value="10" selected>10</option>
                                            <option  value="25">25</option>
                                            <option  value="50">50</option>
                                            <option  value="100">100</option>
                                            <option  value="250">250</option>
                                        </select>
                                    </div>
                                    
                                </div>
                                
                            </div> --}}
                            <div class="col-md-12 d-flex justify-content-between">
                                <!-- Left section (Checkbox and Bulk Apply Tags) -->
                                <div class="d-flex align-items-center ms-4">
                                    <input type="checkbox" class="me-2" name="checkAllLead" id="checkAllLead">
                                    <select id="dropdownApplyTagsToSelected" class="form-control custom-select-sm ms-3" style="width:200px" disabled>
                                        <option value="" selected disabled>Bulk Apply tags</option>
                                    </select>
                                </div>
                            
                                <!-- Right section (Buttons and dropdowns) -->
                                <div class="d-flex align-items-center ms-auto flex-wrap gap-2">

                                    <!-- Select Lists Button -->
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle square-btn" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                            Select Lists
                                        </button>
                                        <div class="dropdown-menu p-2" style="max-height: 25vh; overflow-y: auto;">
                                            <h6 class="dropdown-header">My Lists</h6>
                                            <ul id="sorucesUl" class="list-unstyled mb-2"></ul>
                                            <a class="dropdown-item" id="selectToggleButton"></a>
                                        </div>
                                    </div>

                                    <!-- Select List Type Button -->
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle square-btn" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                            Select List Type
                                        </button>
                                        <div class="dropdown-menu p-2" style="max-height: 25vh; overflow-y: auto;">
                                            <form>
                                                <div class="form-check">
                                                    <input type="checkbox" value="Top Shelf Leads" id="topShelfCheckbox"> <label for="topShelfCheckbox">Top Shelf Leads</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" value="Honorable Mentions" id="honorableCheckbox"> <label for="honorableCheckbox">Honorable Mentions</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" value="Replenishables" id="replenCheckbox"> <label for="replenCheckbox">Replenishables</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" value="Only Bundles" id="onlyBundlesCheckbox"> <label for="onlyBundlesCheckbox">Only Show Bundles</label>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Select Tags Button -->
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle square-btn" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true" onclick="tagsGet()">
                                            Select Tags
                                        </button>
                                        <div class="dropdown-menu p-2" style="max-height: 25vh; overflow-y: auto;">
                                            <div class="input-group input-group-sm mb-2">
                                                <input type="text" class="form-control" id="iconrightInput" placeholder="Search">
                                                <span class="input-group-text"><i class="ri-search-line"></i></span>
                                            </div>
                                            <div id="tags_get" class="mb-2"></div>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#" onclick="unCheckTags(); return false;"><i class="ri-close-line text-primary me-2"></i> Deselect All Tags</a>
                                            <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#manage-tag-modal" onclick="tagsList(); return false;"><i class="ri-pencil-line text-primary me-2"></i> Manage Tags</a>
                                        </div>
                                    </div>

                                    <!-- Add Lead Button -->
                                    <button class="btn btn-outline-primary square-btn" type="button" data-bs-toggle="modal" data-bs-target="#exampleModalScrollable" onclick="formLeadClear()">
                                        Add Lead
                                    </button>

                                    <!-- Page Size Dropdown -->
                                    <div class="input-group input-group-sm" style="width: auto;">
                                        <label for="perPageSelect" class="input-group-text">Page Size:</label>
                                        <select id="perPageSelect" class="form-select select2" style="width: auto; min-width: 70px;">
                                            <option value="10" selected>10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                            <option value="250">250</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                        </th>
                    </tr>
                </thead>
            </table>


        </div>
        <div class="col-md-12 d-none" id="tableViewDev">
            <div class="table-responsive">
                <table id="leads-table-view" class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="sd-compact-td sd-compact-actions"></th>
                            <th class="sd-compact-td sd-compact-type">Type</th>
                            <th class="sd-compact-td sd-compact-name">Name</th>
                            <th class="sd-compact-td sd-compact-tags">Tags</th>
                            <th class="sd-compact-td sd-compact-list_group_id">List</th>
                            <th class="sd-compact-td sd-compact-cost">Buy Cost</th>
                            <th class="sd-compact-td sd-compact-selling_price">FBA Price</th>
                            <th class="sd-compact-td sd-compact-net_profit">Net Profit</th>
                            <th class="sd-compact-td sd-compact-roi">ROI</th>
                            <th class="sd-compact-td sd-compact-ninety_day_average">90 Day Avg</th>
                            <th class="sd-compact-td sd-compact-category">Category</th>
                            <th class="sd-compact-td sd-compact-supplier">Supplier</th>
                            <th class="sd-compact-td sd-compact-asin">ASIN</th>
                            <th class="sd-compact-td sd-compact-promo">Promo</th>
                            <th class="sd-compact-td sd-compact-coupon_code">Coupon Code</th>
                            <th class="sd-compact-td sd-compact-list_item_note">Lead Notes</th>
                        </tr>
                    </thead>
                </table>
            </div>
            
        </div>

        {{-- {{ $leads->links('pagination::bootstrap-5') }} --}}
    </div>

    @include('modals.leads.add-modal')
    @include('modals.create-order-modal')
    
@endsection

@section('script')
    @include('uploads.js.source-js')
    @include('leads.js.lead-js')
    @include('leads.js.tags-js')
    <script>
        var savedPageSizeNew = sessionStorage.getItem("pageSize");
        savedPageSizeNew = savedPageSizeNew !== null && savedPageSizeNew !== undefined ? savedPageSizeNew : '10';
        var startDateFromURL = '';
        var endDateFromURL = '';
        var user_id = '';
        $(document).ready(function () {
            let startOfWeek = moment().startOf('isoWeek');
            let endOfWeek = moment().endOf('isoWeek');
            let today = moment();
            let startOfLastThreeMonths = today.clone().subtract(3, 'months').startOf('month');
            let endOfLastThreeMonths = today.clone().subtract(1, 'months').endOf('month');

            // Parse URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            startDateFromURL = urlParams.get('start_date');
            endDateFromURL = urlParams.get('end_date');
            user_id = urlParams.get('user_id');

            // Determine the start and end date
            const startDate = startDateFromURL 
                ? moment(startDateFromURL, 'YYYY-MM-DD') 
                : moment().subtract(89, 'days');

            const endDate = endDateFromURL 
                ? moment(endDateFromURL, 'YYYY-MM-DD') 
                : moment();

            // Set hidden input values
            $('#start_date').val(startDate.format('YYYY-MM-DD'));
            $('#end_date').val(endDate.format('YYYY-MM-DD'));

            // Initialize the daterangepicker
            $('#dateRangePicker').daterangepicker({
                autoUpdateInput: true,
                locale: { 
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                },
                startDate: startDate,
                endDate: endDate,
                alwaysShowCalendars: true,
                ranges: {
                    'All': [moment('2000-01-01'), moment()],
                    'Today': [moment(), moment()],
                    'This Week': [moment().startOf('week'), moment().endOf('week')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'Last 90 Days': [moment().subtract(89, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Last 3 Months': [moment().subtract(2, 'month').startOf('month'), moment().endOf('month')],
                    'Last 6 Months': [moment().subtract(5, 'month').startOf('month'), moment().endOf('month')],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                    'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
                }
            }, function(start, end, label) {
                $('#dateRangePicker').val(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
                $('#start_date').val(start.format('YYYY-MM-DD'));
                $('#end_date').val(end.format('YYYY-MM-DD'));
                $('#filterTitle').text(label);
            });

            // Force update initial visible value in picker
            $('#dateRangePicker').val(startDate.format('YYYY-MM-DD') + ' - ' + endDate.format('YYYY-MM-DD'));
            $('#filterTitle').text(startDateFromURL && endDateFromURL ? 'Custom Range' : 'Last 90 Days');

            // Handle cancel
            $('#dateRangePicker').on('cancel.daterangepicker', function () {
                $(this).val('');
                $('#start_date').val('');
                $('#end_date').val('');
            });
        });
        var checkedValueTags = [];
        $(document).ready(function () {
            checkedValueTags = $('.checked-input:checked').map(function() {
                return $(this).val();
            }).get();
            var dd =$('.soruce-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            let tableData = $('#leads-table').DataTable({
                // Move the info maessage (i) to the top
                processing: true,
                serverSide: true,
                // orderable:false,
                pageLength: savedPageSizeNew,  // default page size
                ordering: false, // Disable sorting for all columns
                ajax: {
                url: "{{ route('leads.data') }}",
                data: function (d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.sort_by_new = $('#inputSortSelect').val();
                        d.order_in = $('#orderbyinput').val();
                        d.roi_min = $('#roiInputMin').val();
                        d.roi_max = $('#roiInputMax').val();
                        d.net_profit_min = $('#netProfitInputMin').val();
                        d.net_profit_max = $('#netProfitInputMax').val();
                        d.low_fba_min = $('#lowFBAInputMin').val();
                        d.low_fba_max = $('#lowFBAInputMax').val();
                        d.ninety_day_avg_min = $('#ninetyDayAvgInputMin').val();
                        d.ninety_day_avg_max = $('#ninetyDayAvgInputMax').val();
                        d.selected_soruce_ids = $('.soruce-checkbox:checked').map(function() {
                            return $(this).val();
                        }).get();
                        d.checkedValueTags = checkedValueTags;
                        d.user_id = user_id;
                    },
                },
                columns: [
                    {
                        data: null, // Use null since we will render the data manually
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            // Generate the card layout
                            return `<div class="card itemcard" id="item_${data.id}" onclick="activeCard(${data.id})">
                                <div class="card-header">
                                    <div class="row d-flex justify-content-between">
                                        <div class="col-md-8 d-flex">
                                            <div class="me-2 ms-2 mt-1">
                                                <input class="form-check-input" type="checkbox" name="lead_check" id="leadCheck${data.id}" value="${data.id}" onchange="singleCheck(${data.id})">
                                            </div>
                                            <span>
                                                ${data.bundle == 1 ? `
                                                    <i class="ri-add-circle-fill" id="bundleShow_${data.id}" style="font-size:17px; cursor: pointer;" onclick="toggleBundle(${data.id}, true)" title="Show Bundle Items"></i>
                                                    <i class="ri-indeterminate-circle-fill" id="bundleHide_${data.id}" style="font-size:17px; cursor: pointer; display: none;" onclick="toggleBundle(${data.id}, false)" title="Hide Bundle Items"></i>
                                                    <i class="ri-handbag-fill mx-1" title="Bundle" style="font-size:17px;"></i>
                                                ` : ''}
                                                ${data.is_disputed == 1 ? `
                                                    <i class="text-danger ri-indeterminate-circle-fill" title="Item data may be disputed" style="font-size:17px;"></i>
                                                ` : ''}
                                                <i class="text-primary ri-user-add-fill" style="font-size:17px;" title="Your Uploaded Data"></i>
                                                ${data.is_hazmat == 1 ? `
                                                    <i class="text-danger ri-alert-fill mx-1" title="Hazmat Item" style="font-size:17px;"></i>
                                                ` : ''}
                                                ${data.name} &mdash; ${data.date !=null?moment(data.date).format('MMM Do, YYYY'):''} |
                                                <span><b>${data.source.list_name}</b></span>
                                                <br>
                                                <div class="tags">
                                                    <div id="leadTag_${data.id}" style="display: inline;">
                                                        ${data.lead_tags && data.lead_tags.length > 0 ? data.lead_tags.map(tag => `
                                                            <span class="badge bg-${tag.color}">${tag.name}</span>
                                                        `).join('') : ''}
                                                    </div>
                                                    <a class="ms-2 text-primary" style="display: inline;" data-bs-toggle="modal"
                                                        data-bs-target="#select-tag-modal"
                                                        onclick="asinNumber('${data.asin}',${data.id}, '${data.tags}')"
                                                        style="cursor: pointer;">
                                                        <b>+ Add Tags</b>
                                                    </a>
                                                </div>
                                            </span>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex justify-content-end">
                                                <button class="btn btn-light me-1 d-flex"  data-bs-toggle="tooltip" data-bs-placement="top" title="Show Latest Stats">
                                                    <i class=" ri-time-line"></i>
                                                    <div class="form-check form-switch ms-2">
                                                        <input type="checkbox" class="form-check-input" role="switch">
                                                    </div>
                                                </button>
                                                <button class="btn btn-light me-1"  data-bs-toggle="tooltip" data-bs-placement="top" title="Create Order" onclick="openModal(${data.id})"><i class="ri-external-link-line"></i></button>
                                                <button class="btn btn-success me-1" onclick="opneBuyListModal(${data.id})"><i class="ri-money-dollar-box-line"  data-bs-toggle="tooltip" data-bs-placement="top" title="Add to Buy"></i></button>
                                                <button class="btn btn-primary me-1"> <a class="text-white" href="https://keepa.com/#!product/1-${data.asin}" target="_blank">+Keepa</a> </button>
                                                <button class="btn btn-light" data-bs-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">
                                                    <i class="mdi mdi-dots-vertical fs-5 ms-2"></i>

                                                </button>
                                                <div class="dropdown-menu">
                                                    <input type="hidden" name="asinCard${data.id}"  id="asinDataCard${data.id}" value="${data.asin}">
                                                    <input type="hidden" name="nameCard${data.id}"  id="nameDataCard${data.id}" value="${data.name}">
                                                    <a class="dropdown-item" style="cursor:pointer;"  onclick="copyToClipBoard(${data.id},'card')">
                                                        <i class="ri-file-copy-fill text-info me-2"></i>Copy to ClipBoard
                                                    </a>
                                                    <a class="dropdown-item" style="cursor:pointer;"  data-bs-toggle="modal" data-bs-target="#select-tag-modal"
                                                        onclick="asinNumber('${data.asin}', ${data.id},'${data.tags}')">
                                                        <i class="ri-price-tag-3-fill text-primary me-2"></i>Change Tags
                                                    </a>
                                                    <a class="dropdown-item" style="cursor:pointer;"  data-bs-toggle="modal"
                                                        data-bs-target="#exampleModalScrollable"
                                                        onclick="leadFind(${data.id}), fetchSources(${data.source_id})">
                                                        <i class="ri-pencil-line text-primary me-2"></i>Update Lead
                                                    </a>
                                                    <a class="dropdown-item" style="cursor:pointer;"  onclick="leadDelete(${data.id})">
                                                        <i class="ri-delete-bin-line text-danger me-2"></i>Delete Lead
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col text-center">
                                            <small>Buy Cost</small><br>
                                            <span class="fw-bold text-primary">$${data.cost || 'N/A'}</span>
                                        </div>
                                        <div class="col text-center">
                                            <small>FBA Price</small><br>
                                            <span class="fw-bold text-primary">$${data.sell_price || 'N/A'}</span>
                                        </div>
                                        <div class="col text-center">
                                            <small>Net Profit</small><br>
                                            <span class="fw-bold text-success">$${data.net_profit || 'N/A'}</span>
                                        </div>
                                        <div class="col text-center">
                                            <small>ROI</small><br>
                                            <span class="fw-bold text-info">${data.roi || 'N/A'}%</span>
                                        </div>
                                        <div class="col text-center">
                                            <small>90 Day Average</small><br>
                                            <span class="fw-bold text-info">${data.bsr || 'N/A'}</span>
                                        </div>
                                        <div class="col text-center">
                                            <small>Category</small><br>
                                            <span class="fw-bold text-info">${data.category || 'N/A'}</span>
                                        </div>
                                        <div class="col text-center">
                                            <small>Supplier</small><br>
                                            <span class="fw-bold text-info">
                                                <a target="_blank" href="${data.url}" class="mt-3">
                                                ${data.supplier || 'N/A'} </a> </span>
                                        </div>
                                        <div class="col text-center">
                                            <a href="javascript:void(0);" onclick="redirectLinks('${data.url}', '${data.asin}')" class="mt-3">
                                                <i class="ri-external-link-line text-primary fs-4"></i>
                                            </a>
                                        </div>
                                        <div class="col text-center">
                                            <small>Asin</small><br>
                                            <span class="fw-bold text-info">
                                                <a target="_blank" href="https://www.amazon.com/dp/${data.asin}"> ${data.asin || 'N/A'}</a>
                                            </span>
                                        </div>
                                    </div>
                                    <hr class="d-none bottoom-row_${data.id}">
                                    <div class="row d-none bottoom-row_${data.id}" id="">
                                        <div class="col-4 text-center">
                                            <span class="">PROMO</span><br>
                                            <span class="fw-bold text-dark">${data.promo ?? 'N/A'}</span>
                                        </div>
                                        <div class="col-4 text-center">
                                            <span class="">COUPON CODE</span><br>
                                            <span class="fw-bold text-dark">${data.coupon ?? 'N/A'}</span>
                                        </div>
                                        <div class="col-4 text-center">
                                            <span class="">LEAD NOTES</span><br>
                                            <span class="fw-bold text-dark">${data.notes ?? 'N/A'}</span>
                                        </div>
                                    </div>
                                    <hr class="d-none bottoom-row_${data.id}">
                                    ${generateBundleContent(data)}
                                </div>
                            </div>`;
                        },
                    },
                ],
                dom: '<"top"fip><"clear">rt<"bottom"p><"clear">',
                language: {
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                },
            }).on('xhr.dt', function (e, settings, json, xhr) {
                if (json.total_leads !== undefined) {
                    $('#leadsCount').text(json.total_leads); // Update count in your UI
                    

                }
            });
             // Update DataTable page length when the custom "Page Size" dropdown changes
            $('#perPageSelect').change(function() {
                var newPageSize = $(this).val();  // Get the selected value
                tableData.page.len(newPageSize).draw();  // Update DataTable's page length and redraw
            });
            $(document).on('change', '.soruce-checkbox', function() {
                // Update the table in real-time
                tableData.ajax.reload();
            });
            let tableDataTableView = $('#leads-table-view').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('table-view-data') }}", // Replace with your backend route
                    type: "GET",
                    data: function (d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.sort_by_new = $('#inputSortSelect').val();
                        d.order_in = $('#orderbyinput').val();
                        d.roi_min = $('#roiInputMin').val();
                        d.roi_max = $('#roiInputMax').val();
                        d.net_profit_min = $('#netProfitInputMin').val();
                        d.net_profit_max = $('#netProfitInputMax').val();
                        d.low_fba_min = $('#lowFBAInputMin').val();
                        d.low_fba_max = $('#lowFBAInputMax').val();
                        d.ninety_day_avg_min = $('#ninetyDayAvgInputMin').val();
                        d.ninety_day_avg_max = $('#ninetyDayAvgInputMax').val();
                    },
                },
                columns: [
                    { data: "actions", name: "actions", orderable: false, searchable: false },
                    { data: "itemType", name: "itemType" ,orderable: false, searchable: false},
                    { data: "name", name: "name" },
                    { data: "tags", name: "tags" ,orderable: false, searchable: false},
                    { data: "date", name: "date" },
                    { data: "cost", name: "cost" },
                    { data: "sale_price", name: "sale_price" },
                    { data: "net_profit", name: "net_profit" },
                    { data: "roi", name: "roi" },
                    { data: "bsr", name: "bsr" },
                    { data: "category", name: "category" },
                    { data: "supplier", name: "supplier" },
                    { data: "asin", name: "asin" },
                    { data: "promo", name: "promo" },
                    { data: "coupon", name: "coupon" },
                    { data: "notes", name: "notes" },
                ],
                columnDefs: [
                    { targets: [0], orderable: false }, // Disable sorting for actions column
                ],
                // order: [[1, 'asc']], // Default sorting
                // pageLength: , // Default number of rows per page
            });
            $('#dropdownApplyTagsToSelected').on('change',function(){
                if($(this).val() != 'blank'){
                    const selectedRows = $('input[name="lead_check"]:checked').map(function () {
                        return $(this).val();
                    }).get();
                    var tags = $(this).val();
                    $.ajax({
                        url:"{{ url('update-bluk-tag') }}",
                        type:"POST",
                        data:{
                            tag:tags,
                            leadIds:selectedRows,
                            "_token": "{{ csrf_token() }}"
                        },
                        success:function(data){
                            // console.log(data);
                            tableData.ajax.reload();
                            tableDataTableView.ajax.reload();
                            $('#checkAllLead').prop('checked',false);
                            $('#dropdownApplyTagsToSelected').empty();
                            $('#dropdownApplyTagsToSelected').prop('disabled',true);

                            $('#dropdownApplyTagsToSelected').append('<option value="" selected disabled>Bulk Apply tags</option>');

                        }
                    })
                }
            })
            // Apply Filters on Button Click
            $('#apply-filter').on('click', function () {
                checkedValueTags = $('.checked-input:checked').map(function() {
                    return $(this).val();
                }).get();
                tableData.ajax.reload();
            });
        });
        const generateBundleContent = (data) => {
            if (data.bundle === 1) {
                let bundleContent = `<div class="row" id="bundle_row_${data.id}" style="display: none;">`;

                // Loop through the bundles data and generate the HTML for each bundle
                data.bundles.forEach(item => {
                bundleContent += `
                    <div class="col-md-4">
                    <div class="card child-card mb-5">
                        <div class="card-body">
                        <div class="row">
                            <div class="col text-center">
                            <p class="text-muted">Buy Cost</p>
                            <p>$${item.cost}</p>
                            </div>
                            <div class="col text-center">
                            <p class="text-muted">Supplier</p>
                            <p><a href="${item.url}" target="_blank" class="text-decoration-none">${item.supplier}</a></p>
                            </div>
                            <div class="col text-center">
                            <p class="text-muted">COUPON CODE</p>
                            <p>${item.coupon}</p>
                            </div>
                            <div class="w-100 d-none d-sm-block d-md-none"></div>
                            <div class="col text-center">
                            <p class="text-muted">PROMO</p>
                            <p>${item.promo}</p>
                            </div>
                            <div class="col text-center">
                            <p class="text-muted">Note</p>
                            <p>${item.notes}</p>
                            </div>
                        </div>
                        <p class="card-text text-end"><small class="text-muted">${data.asin}</small></p>
                        </div>
                    </div>
                    </div>
                `;
                });

                bundleContent += `</div>`;
                return bundleContent;
            }
            return '';
        };
        function activeCard(id){
            $('.itemcard').removeClass('active');
            $('#item_'+id).addClass('active');
        }
        function singleCheck(id){
            checkSelected()  
        }
        function checkSelected(){
            const selectedRows = $('input[name="lead_check"]:checked').map(function () {
                return $(this).val();
            }).get();
            if(selectedRows.length >0){
                $('#dropdownApplyTagsToSelected').prop('disabled',false);
                tagsGet();
            }else{
                $('#dropdownApplyTagsToSelected').empty();
                $('#dropdownApplyTagsToSelected').prop('disabled',true);
                $('#dropdownApplyTagsToSelected').append('<option value="" selected disabled>Bulk Apply tags</option>');
            }
        }
        $('#checkAllLead').on('change',function(){
            if($(this).is(":checked")){
                $('input[name="lead_check"]').prop('checked',true);
            }else{
                $('input[name="lead_check"]').prop('checked',false);
            }
            checkSelected();
        })
        $('#hideFilters').on('change',function(){
            if($(this).is(':checked')){
                $('.filterClass').addClass('d-none')
            }else{
                $('.filterClass').removeClass('d-none')
            }
        })
        $('#compactView').on('change',function(){
            if($(this).is(':checked')){
                $('#tableViewDev').removeClass('d-none')
                $('#cardView').addClass('d-none')
            }else{
                $('#tableViewDev').addClass('d-none')
                $('#cardView').removeClass('d-none')
            }
        })
        function copyToClipBoard(id,type="card") {
            if(type == 'card'){
                var asin = $('#asinDataCard'+id).val();
                var name = $('#nameDataCard'+id).val();
                var textToCopy = asin + '  ' + name;
            }else{
                var asin = $('#asinDataTable'+id).val();
                var name = $('#nameDataTable'+id).val();
                var textToCopy = asin + '  ' + name;
            }
            const tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(textToCopy).select();
            document.execCommand('copy');
            tempInput.remove();
            toastr.success('Copied');
        }
        $(document).on('click', function (event) {
            if (!$(event.target).closest('#collapseExample, [data-bs-toggle="collapse"]').length) {
                $('#collapseExample').collapse('hide');
            }
        });

    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Get saved values from sessionStorage or localStorage
            const savedPageSize = sessionStorage.getItem("pageSize");
            const savedSearchQuery = sessionStorage.getItem("searchQuery");
    
            // Apply saved page size to the perPageSelect dropdown
            if (savedPageSize) {
                $('#perPageSelect').val(savedPageSize).trigger('change');
            }
    
            // Apply saved search query to the search input field
            if (savedSearchQuery) {
                document.getElementById("iconrightInput").value = savedSearchQuery;
            }
    
            // Event listener to save selected per page value to sessionStorage
            document.getElementById("perPageSelect").addEventListener("change", function() {
                sessionStorage.setItem("pageSize", this.value);
            });
    
            // Event listener to save search query to sessionStorage
            document.getElementById("iconrightInput").addEventListener("input", function() {
                sessionStorage.setItem("searchQuery", this.value);
            });
        });
        function redirectLinks(url, asin) {
            // Create two links in the same click event
            let win1 = window.open(url, '_blank');
            let win2 = window.open(`https://www.amazon.com/dp/${asin}`, '_blank');

            // Fallback if popup is blocked
            if (!win1 || win1.closed || typeof win1.closed == 'undefined') {
                alert("Popup blocked! Please allow popups for this site.");
            }
        }
        $(document).ready(function () {
            setTimeout(() => {
                $('#apply-filter').click();
            }, 400);
        });
        function getUrlParams() {
            const params = {};
            const searchParams = new URLSearchParams(window.location.search);
            for (const [key, value] of searchParams.entries()) {
                params[key] = value;
            }
            return params;
        }

    </script>
    
@endsection
