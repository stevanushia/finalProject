@extends('layouts.admin')

@section('title', 'Financial Reports')
@section('header', 'Financial Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold text-dark">Financial Overview</h4>
        <p class="text-muted mb-0">Track revenue and transaction history</p>
    </div>
    <a href="{{ route('admin.reports.financial.export') }}" class="btn btn-danger fw-bold shadow-sm">
        <i class="fas fa-file-pdf me-2"></i> Export Report
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Total Revenue</small>
                <h3 class="fw-bold text-dark mb-0">Rp {{ number_format($data['summary']['total_revenue'], 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Total Transactions</small>
                <h3 class="fw-bold text-dark mb-0">{{ number_format($data['summary']['total_txns']) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Success Rate</small>
                <h3 class="fw-bold text-dark mb-0">{{ $data['summary']['success_rate'] }}%</h3>
            </div>
        </div>
    </div>
    
    {{-- REMOVED: Avg. Transaction Card --}}
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold m-0">Revenue Growth (Monthly)</h6>
            </div>
            <div class="card-body">
                <canvas id="revenueTrendChart" style="height: 300px; width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold m-0">Payment Methods</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="height: 250px; width: 100%;">
                    <canvas id="paymentMethodChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0">Transaction History</h6>
                <span class="badge bg-primary">{{ count($data['table']) }} Records</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    {{-- ADDED ID="transactionsTable" HERE --}}
                    <table id="transactionsTable" class="table table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['table'] as $txn)
                                <tr>
                                    <td data-sort="{{ $txn['timestamp'] ?? 0 }}">
                                        {{ \Carbon\Carbon::createFromTimestampMs($txn['timestamp'])->format('M d, Y H:i') }}
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $txn['displayName'] ?? 'Unknown' }}</div>
                                        <small class="text-muted">{{ $txn['email'] ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ ucwords(str_replace('_', ' ', $txn['subscriptionType'] ?? 'N/A')) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ ucwords(str_replace('_', ' ', $txn['paymentMethod'] ?? '-')) }}
                                    </td>
                                    <td class="fw-bold" data-order="{{ $txn['amount'] ?? 0 }}">
                                        Rp {{ number_format($txn['amount'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        @php
                                            $status = $txn['status'] ?? 'unknown';
                                            $badgeClass = match($status) {
                                                'success', 'settlement', 'capture' => 'bg-success',
                                                'pending' => 'bg-warning text-dark',
                                                'failed', 'deny', 'cancel', 'expire' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} rounded-pill px-3">
                                            {{ strtoupper($status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                {{-- DataTables handles empty states better, but we keep this just in case --}}
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
    .dataTables_wrapper .dataTables_length select {
        padding-right: 25px;
    }
    .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- INJECT SCRIPTS FOR DATATABLES --}}
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    // 1. Initialize DataTable
    $(document).ready(function() {
        $('#transactionsTable').DataTable({
            "order": [[ 0, "desc" ]], // Sort by 1st column (Date) descending
            "pageLength": 10,
            "language": {
                "search": "Filter records:"
            }
        });
    });

    // 2. Revenue Trend Chart
    new Chart(document.getElementById('revenueTrendChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($data['charts']['revenue']['labels']) !!},
            datasets: [{
                label: 'Revenue (IDR)',
                data: {!! json_encode($data['charts']['revenue']['data']) !!},
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                x: { grid: { display: false } }
            }
        }
    });

    // 3. Payment Methods Chart
    new Chart(document.getElementById('paymentMethodChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($data['charts']['methods']['labels']) !!},
            datasets: [{
                data: {!! json_encode($data['charts']['methods']['data']) !!},
                backgroundColor: ['#0d6efd', '#6610f2', '#fd7e14', '#20c997', '#ffc107'],
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