@extends('layouts.app')

@section('title', 'Leads')

@section('content')
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
                        <li class="breadcrumb-item active"><a href="{{ url('leads') }}"><h3>Leads</h3></a></li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <div class="row">
        <div class="row align-items-center justify-content-between">
            <div class="col-md-6">
              <h3>OA Feta List #1</h3>
              <div class="row">
                <div class="col-2">
                  <p class="lead">Select List:</p>
                </div>
                <div class="col-4">
                  <div class="dropdown">
                    <select name="" class="form-control select2" id="">
                        <option></option>
                    </select>
                  </div>
                </div>
                <div class="col-3">
                  <button type="button" class="btn btn-outline-primary btn-sm mt-1">Download Data</button>
                </div>
              </div>
            </div>
          
            <div class="col-md-6">
              <div class="row g-3">
                <div class="col">
                  <div class="card text-center">
                    <div class="card-body">
                      <h5 class="card-title">Cost</h5>
                      <h2 class="card-text">$13.41</h2>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="card text-center">
                    <div class="card-body">
                      <h5 class="card-title">Selling Price</h5>
                      <h2 class="card-text">$28.65</h2>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="card text-center">
                    <div class="card-body">
                      <h5 class="card-title">Net Profit</h5>
                      <h2 class="card-text">$4.61</h2>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="card text-center">
                    <div class="card-body">
                      <h5 class="card-title">ROI</h5>
                      <h2 class="card-text">37.11%</h2>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
        
    </div>
    
    <div class="row mt-3">
        <div id="tags-alert"></div>
        <div class="col-md-12">
            <table id="leads-table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Top Shelf Items
                        </th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    @include('modals.leads.add-modal')
    @include('modals.create-order-modal')
    
@endsection

@section('script')
    @include('uploads.js.source-js')
    @include('leads.js.lead-js')
    @include('leads.js.tags-js')
    <script>
        $(document).ready(function () {
        let tableData = $('#leads-table').DataTable({
            processing: true,
            serverSide: true,
            paging: false, // Disable pagination
            searching: false, // Disable search
            info: false, // Disable table info like "Showing 1 to 10 of 100 entries"
            lengthChange: false, // Disable the option to change the number of visible rows
            ordering: false, // Disable sorting for all columns

            ajax: {
            url: "{{ route('get.top.shelf.leads') }}",
            data: function (d) {
                    d.date_range = $('#inputDateOption').val();
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
            pageLength: 10, // Set the default number of records to display
            columns: [
                {
                    data: null, // Use null since we will render the data manually
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        // Generate the card layout
                        return `
                            <div class="card">
                                <div class="card-header">
                                <div class="row d-flex justify-content-between">
                                        <div class="col-md-8 d-flex">
                                            
                                            <span>
                                                ${data.bundle == 1 ? `
                                                    <i class="ri-add-circle-fill" id="bundleShow_${data.id}" style="font-size:17px; cursor: pointer;" onclick="toggleBundle(${data.id}, true)" title="Show Bundle Items"></i>
                                                    <i class="ri-indeterminate-circle-fill" id="bundleHide_${data.id}" style="font-size:17px; cursor: pointer; display: none;" onclick="toggleBundle(${data.id}, false)" title="Hide Bundle Items"></i>
                                                    <i class="ri-handbag-fill mx-1" title="Bundle" style="font-size:17px;"></i>
                                                ` : ''}
                                                ${data.is_disputed == 1 ? `
                                                    <i class="text-danger ri-indeterminate-circle-fill" title="Item data may be disputed" style="font-size:17px;"></i>
                                                ` : ''}
                                                <i class="bx bxs-trophy mt-1"  style="font-size:17px;color:yellow" title="Top Shelf Leads"></i>
                                                ${data.is_hazmat == 1 ? `
                                                    <i class="text-danger ri-alert-fill mx-1" title="Hazmat Item" style="font-size:17px;"></i>
                                                ` : ''}
                                                ${data.name} &mdash; |
                                                <span><b>${data.source.list_name}</b></span>
                                                <br>
                                               
                                            </span>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex justify-content-end">
                                               
                                            
                                                <button class="btn btn-success me-1" onclick="opneBuyListModal(${data.id})"><i class="ri-money-dollar-box-line"  data-bs-toggle="tooltip" data-bs-placement="top" title="Add to Buy"></i></button>
                                                <button class="btn btn-secondary me-1" onclick="reportError(${data.id})"><i class=" bx bxs-flag"  data-bs-toggle="tooltip" data-bs-placement="top" title="Report Error"></i></button>
                                                
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
                                            <span class="fw-bold text-success">$${data.sell_price || 'N/A'}</span>
                                        </div>
                                        <div class="col text-center">
                                            <small>Net Profit</small><br>
                                            <span class="fw-bold text-danger">$${data.net_profit || 'N/A'}</span>
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
                                            <span class="fw-bold text-info">${data.supplier || 'N/A'}</span>
                                        </div>
                                        <div class="col text-center">
                                            <a href="${data.url}" class="mt-3">
                                                &lt;
                                                <i class="ri-external-link-line text-primary fs-4"></i>
                                                &gt;
                                            </a>
                                        </div>
                                        <div class="col text-center">
                                            <small>Asin</small><br>
                                            <span class="fw-bold text-info">
                                                <a target="_blank" href="https://www.amazon.com/dp/${data.asin}"> ${data.asin || 'N/A'}</a>
                                            </span>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
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
                                    <hr>
                                    ${generateBundleContent(data)}
                                </div>
                            </div>`;
                    },
                },
            ],
        });
         // Apply Filters on Button Click
        $('#apply-filter').on('click', function () {
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
    </script>
@endsection
