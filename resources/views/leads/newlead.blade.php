@extends('layouts.app')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Sources</h3>
    </div>

    <div class="card shadow-sm border-0">

        <div class="table-responsive">

            <table class="table align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Source Name</th>
                        <th>Date</th>
                        <th>Total Leads</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($sources as $source)

                        <tr>
                            <td>{{ $source->id }}</td>

                            <td>
                                <strong>{{ $source->list_name }}</strong>
                            </td>

                            <td>
                                {{ $source->date ? \Carbon\Carbon::parse($source->date)->format('d M Y') : '-' }}
                            </td>

                            <td>
                                <span class="badge bg-primary">
                                    {{ $source->leads_count }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('sources.leads', $source->id) }}"
                                   class="btn btn-sm btn-dark">
                                    View Leads
                                </a>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="text-center py-5">
                                No Sources Found
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    <div class="mt-4">
        {{ $sources->links() }}
    </div>

</div>

@endsection