@extends('layouts.admin')

@section('title', 'Game Reports')
@section('header', 'Game Analytics')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Total Sessions</small>
                <h3 class="fw-bold text-dark mb-0">{{ number_format($data['counts']['total']) }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Completed</small>
                <h3 class="fw-bold text-dark mb-0">{{ number_format($data['counts']['completed']) }}</h3>
                <small class="text-success">
                    {{ $data['counts']['total'] > 0 ? round(($data['counts']['completed'] / $data['counts']['total']) * 100) : 0 }}% Rate
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Avg. Total Points</small>
                <h3 class="fw-bold text-dark mb-0">{{ $data['counts']['avg_score'] }}</h3>
                <small class="text-muted">Per Game</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Highest Score</small>
                <h3 class="fw-bold text-dark mb-0">{{ $data['counts']['high_score'] }}</h3>
                <small class="text-muted">Combined Pts</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold m-0">Session Status</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="height: 250px; width: 100%;">
                    <canvas id="gameStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0">Recent Game Sessions</h6>
                <span class="badge bg-primary">{{ count($data['table']) }} Games</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="gamesTable" class="table table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Session Name</th>
                                <th>Teams</th>
                                <th>Score</th>
                                <th>Status</th>
                                <th>Last Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['table'] as $game)
                                <tr>
                                    <td class="fw-bold">{{ $game['name'] }}</td>
                                    <td>
                                        <span class="text-primary fw-semibold">{{ $game['home'] }}</span>
                                        <span class="text-muted mx-1">vs</span>
                                        <span class="text-danger fw-semibold">{{ $game['away'] }}</span>
                                    </td>
                                    <td class="fw-bold fs-6">{{ $game['score'] }}</td>
                                    <td>
                                        @if($game['status'] === 'Completed')
                                            <span class="badge bg-success">COMPLETED</span>
                                        @else
                                            <span class="badge bg-warning text-dark">IN PROGRESS</span>
                                        @endif
                                    </td>
                                    <td data-sort="{{ $game['timestamp'] ?? 0 }}">
                                        @if($game['timestamp'])
                                            {{ \Carbon\Carbon::createFromTimestampMs($game['timestamp'])->diffForHumans() }}
                                        @else
                                            <span class="text-muted small">Unknown</span>
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
        $('#gamesTable').DataTable({
            "pageLength": 5,
            "order": [[ 4, "desc" ]], // Sort by 'Last Active' descending
            "language": { "search": "Search games:" }
        });
    });

    // 2. Status Chart
    new Chart(document.getElementById('gameStatusChart'), {
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
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            }
        }
    });
</script>
@endpush