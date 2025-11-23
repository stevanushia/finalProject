@extends('layouts.admin')

@section('title', 'Tournament Reports')
@section('header', 'Tournament Analytics')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Total Tournaments</small>
                <h3 class="fw-bold text-dark mb-0">{{ number_format($data['counts']['total']) }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Active / Upcoming</small>
                <h3 class="fw-bold text-dark mb-0">{{ number_format($data['counts']['active']) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Total Participants</small>
                <h3 class="fw-bold text-dark mb-0">{{ number_format($data['counts']['total_participants']) }}</h3>
                <small class="text-muted">Teams registered</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Most Popular</small>
                <h3 class="fw-bold text-dark mb-0">{{ $data['counts']['popular_format'] }} Teams</h3>
                <small class="text-muted">Format</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold m-0">Completion Status</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="height: 250px; width: 100%;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold m-0">Format Preference</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="height: 250px; width: 100%;">
                    <canvas id="sizeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0">Tournament History</h6>
                <span class="badge bg-primary">{{ count($data['table']) }} Records</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tournamentTable" class="table table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Size</th>
                                <th>Start Date</th>
                                <th>Status</th>
                                <th>Winner</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['table'] as $t)
                                <tr>
                                    <td class="fw-bold">{{ $t['name'] }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $t['teams'] }} Teams</span>
                                    </td>
                                    <td data-sort="{{ $t['start_date'] ?? 0 }}">
                                        @if($t['start_date'])
                                            {{ \Carbon\Carbon::createFromTimestampMs($t['start_date'])->format('M d, Y') }}
                                        @else
                                            <span class="text-muted small">TBD</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($t['status'] === 'completed')
                                            <span class="badge bg-success">COMPLETED</span>
                                        @else
                                            <span class="badge bg-warning text-dark text-uppercase">{{ $t['status'] }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($t['winner'] !== '-')
                                            <span class="text-success fw-bold"><i class="fas fa-crown me-1"></i> {{ $t['winner'] }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                {{-- DataTables empty state --}}
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- INJECT STYLES FOR DATATABLES --}}
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .dataTables_wrapper .dataTables_length select { padding-right: 25px; }
    .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    // 1. Initialize DataTable
    $(document).ready(function() {
        $('#tournamentTable').DataTable({
            "pageLength": 5,
            "order": [[ 2, "desc" ]], // Sort by Start Date descending
            "language": { "search": "Search tournaments:" }
        });
    });

    // 2. Status Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($data['charts']['status']['labels']) !!},
            datasets: [{
                data: {!! json_encode($data['charts']['status']['data']) !!},
                backgroundColor: ['#198754', '#ffc107'], // Green (Completed), Yellow (Active)
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // 3. Size Preference Chart (Bar Chart)
    new Chart(document.getElementById('sizeChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($data['charts']['sizes']['labels']) !!},
            datasets: [{
                label: 'Number of Tournaments',
                data: {!! json_encode($data['charts']['sizes']['data']) !!},
                backgroundColor: ['#0dcaf0', '#0d6efd', '#6610f2'], // Different blues
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endpush