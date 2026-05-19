@extends('layouts.app')
@section('title', 'New Sources Leads')

@section('styles')


    <style>

        table.dataTable tbody td {
            vertical-align: middle;
        }

        .product-img{
            width:70px;
            height:70px;
            object-fit:contain;
            border-radius:8px;
            background:#f5f5f5;
            padding:4px;
        }

        .table-links a{
            color:#0d6efd;
            text-decoration:none;
        }

        .table-links a:hover{
            text-decoration:underline;
        }

    </style>

@endsection

@section('content')

<div class="row">

    <div class="col-12">

        <div class="page-title-box d-sm-flex align-items-center justify-content-between">

            <h4 class="mb-sm-0">
                Sources Leads
            </h4>

        </div>

    </div>

</div>

<div class="row">

    <div class="col-md-12">

        <div class="card">

            <div class="card-body">

                <div class="row mb-3">

                    <div class="col-md-4">

                        <select id="source_id"
                                class="form-control">

                            <option value="">
                                Select Source
                            </option>

                            @foreach($sources as $source)

                                <option value="{{ $source->id }}">
                                    {{ $source->list_name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

                <div class="table-responsive">

                    <table id="leadTable"
                           class="table table-bordered table-striped nowrap w-100">

                        <thead>

                        <tr>

                            <th>Image</th>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Supplier</th>
                            <th>Category</th>
                            <th>Store URL</th>
                            <th>Amazon URL</th>
                            <th>ASIN</th>
                            <th>Store Price</th>
                            <th>AMZ Price</th>
                            <th>Price Badge</th>
                            <th>Net Profit</th>
                            <th>ROI</th>
                            <th>Current BSR</th>
                            <th>90 Day BSR</th>
                            <th>Sales / Mo</th>
                            <th>FBA Sellers</th>
                            <th>Buy Box</th>
                            <th>Notes</th>
                            <th>Shipping</th>
                            <th>Cashback %</th>
                            <th>Giftcard %</th>

                        </tr>

                        </thead>

                        <tbody></tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection

@section('script')

      <script>
        $(document).ready(function () {
            loadLeads('all');
        });

        let table = $('#leadTable').DataTable({

            processing: true,
            searching: true,
            paging: true,
            ordering: true,
            pageLength: 25,

            columns: [

                { data: 'image' },
                { data: 'date' },
                { data: 'name' },
                { data: 'supplier' },
                { data: 'category' },
                { data: 'url' },

                // amazon_url db mein nahi hai
                // remove or make null safe
                {
                    data: null,
                    defaultContent: '-'
                },

                { data: 'asin' },

                // FIXED
                { data: 'cost' },

                { data: 'sell_price' },

                { data: 'promo' },

                { data: 'net_profit' },

                { data: 'roi' },

                { data: 'bsr' },

                { data: 'bsr_90_day' },

                { data: 'sales_per_month' },

                { data: 'fba_sellers' },

                { data: 'buy_box' },

                { data: 'notes' },

                { data: 'shipping' },

                // FIXED
                { data: 'cashback_percentage' },

                // FIXED
                { data: 'giftcard_percentage' }

            ],

            columnDefs: [

                {
                    targets: 0,

                    render: function(data){

                        if(!data){
                            return '-';
                        }

                        return `
                            <img src="${data}"
                                 class="product-img">
                        `;
                    }
                },

                {
                    targets: 5,

                    render: function(data){

                        if(!data){
                            return '-';
                        }

                        return `
                            <div class="table-links">
                                <a href="${data}"
                                   target="_blank">
                                    Store Link
                                </a>
                            </div>
                        `;
                    }
                },

                {
                targets: 6,

                render: function(data, type, row){

                let asin = row.asin ?? '';

                if(!asin){
                    return '-';
                }

                let amazonUrl = 'https://www.amazon.com/dp/' + asin;

                return `
                    <div class="table-links">
                        <a href="${amazonUrl}"
                            target="_blank">
                            ${asin}
                        </a>
                    </div>
                `;
                }
                },

                {
                    targets: '_all',

                    render: function(data){

                        if(
                            data === null ||
                            data === ''
                        ){
                            return '-';
                        }

                        return data;
                    }
                }

            ]

        });

        /*
        |--------------------------------------------------------------------------
        | LOAD DATA
        |--------------------------------------------------------------------------
        */
        function loadLeads(source_id)
        {
            table.clear().draw();

            $.ajax({

                url: "{{ url('/new-sources/leads') }}/" + source_id,

                type: "GET",

                success:function(res){

                    table.rows.add(res).draw();

                }

            });
        }

        $('#source_id').change(function(){
            let source_id = $(this).val();
            if(source_id == ''){
                source_id = 'all';
            }
            loadLeads(source_id);

        });

    </script>

@endsection