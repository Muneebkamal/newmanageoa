@extends('layouts.app')

@section('title', 'Add Employee')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
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
        </div>
    </div>
@endsection
