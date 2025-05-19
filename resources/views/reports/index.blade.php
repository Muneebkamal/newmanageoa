@extends('layouts.app')

@section('title', 'OaManage - Reports')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Report</a></li>
                    
                </ol>
            </div>

        </div>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-12 d-flex justify-content-between align-items-center">
        <form id="search_form" action="" method="GET" class="d-flex align-items-center w-100">
            @csrf
            <div class="me-2 w-25">
            <label for="date-input">
            Select Date Range:
            </label>
            <input type="text" id="dateRangePicker" class="form-control custom-select-sm" />
            <input type="hidden" id="start_date" name="start_date" />  
            <input type="hidden" id="end_date" name="end_date" />  
            </div>
            <div class="me-2 w-25">
            <label for="employee_id">
            Select Employee:
            </label>
            @php
                $allEmployeeIds = $employees->pluck('id')->implode(',');
            @endphp
            <select id="employee_id" name="employee_id" class="form-control custom-select-sm">
                <option value="{{ $allEmployeeIds }}">-- All Employees --</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                @endforeach
            </select>
                        </div>
            <div class="mt-4 d-flex">
            <button type="submit" id="sub_btn" class="btn btn-primary me-2">Search</button>
            <button type="button" class="btn btn-danger" id="resetButton">Clear</button>
            </div>
        </form>
    </div>
</div>
<div class="row"></div>
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th colspan="2">Total Leads</th>
                                <th colspan="2">Buy List Unit Purchased</th>
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
    $(document).ready(function () {
        // Initialize date range picker with default range (This Week)
        let startOfRange = moment().startOf('month');
        let endOfRange = moment().endOf('month'); // instead of moment()
        $('#dateRangePicker').daterangepicker({
            autoUpdateInput: true,
            locale: { cancelLabel: 'Clear' },
            startDate: startOfRange,
            endDate: endOfRange,
            alwaysShowCalendars: true,
            ranges: {
                'Today': [moment(), moment()],
                'This Week': [moment().startOf('isoWeek'), moment().endOf('isoWeek')], 
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Last 3 Months': [moment().subtract(2, 'month').startOf('month'), moment().endOf('month')],
                'Last 6 Months': [moment().subtract(5, 'month').startOf('month'), moment().endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
                'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
            }
        }, function(start, end) {
            $('#start_date').val(start.format('YYYY-MM-DD'));
            $('#end_date').val(end.format('YYYY-MM-DD'));
        });
        // Set initial values
        $('#start_date').val(startOfRange.format('YYYY-MM-DD'));
        $('#end_date').val(endOfRange.format('YYYY-MM-DD'));
        $('#search_form').on('submit', function (e) {
            e.preventDefault();
            let formData = $(this).serialize();

            $.ajax({
                url: "{{ route('reports.filter') }}",
                type: 'GET',
                data: formData,
                success: function (response) {
                    appendTable(response);
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                }
            });
        });

        $('#resetButton').on('click', function () {
            $('#search_form')[0].reset();
            $('table tbody').empty();
        });
        setTimeout(() => {
            $('#search_form').submit();
        }, 1000);
    });
    function appendTable(data) {
        let tableBody = $('table tbody');
        tableBody.empty();
        const employee_id = $('#employee_id').val() || '';

        data.forEach(item => {
            let row = `<tr>
                <td>${item.date}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="me-2 fw-bold">${item.leads}</div>
                       
                    </div>
                </td>
                <td>
                    <a class="btn btn-sm btn-outline-primary" 
                    href="/leads-new?user_id=${employee_id}&start_date=${item.date}&end_date=${item.date}" 
                    target="_blank">
                        View Leads
                    </a>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="me-2 fw-bold">${item.buylist}</div>
                    </div>
                </td>
                <td>
                    <a class="btn btn-sm btn-outline-success" 
                    href="/buylist?user_id=${employee_id}&start_date=${item.date}&end_date=${item.date}" 
                    target="_blank">
                        View Purchased
                    </a>
                </td>
            </tr>`;
            tableBody.append(row);
        });
    }
    function goToPage(type, userId,date) {
        const start = date;
        const end = date;
        const employee_id = $('#employee_id').val() || '';
        console.log(employee_id)
        const url = ``;
        window.location.href = url;
    }

    </script>
@endsection