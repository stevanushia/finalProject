@extends('layouts.admin')

@section('title', 'User Reports')
@section('header', 'User Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold text-dark">User Analytics</h4>
        <p class="text-muted mb-0">Monitor user growth and demographics</p>
    </div>
    <a href="{{ route('admin.reports.users.export') }}" class="btn btn-danger fw-bold shadow-sm">
        <i class="fas fa-file-pdf me-2"></i> Export Report
    </a>
</div>
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Total Users</small>
                <h3 class="fw-bold text-dark mb-0">{{ number_format($data['counts']['total']) }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Premium Members</small>
                <h3 class="fw-bold text-dark mb-0">{{ number_format($data['counts']['premium']) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Active (30 Days)</small>
                <h3 class="fw-bold text-dark mb-0">{{ number_format($data['counts']['active']) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-danger h-100">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Administrators</small>
                <h3 class="fw-bold text-dark mb-0">{{ number_format($data['counts']['admins']) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold m-0">Membership Distribution</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="height: 250px; width: 100%;">
                    <canvas id="userStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0">User Directory</h6>
                <span class="badge bg-primary">{{ count($data['table']) }} Users</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="usersTable" class="table table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['table'] as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold text-secondary" style="width: 35px; height: 35px;">
                                                {{ strtoupper(substr($user['name'], 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $user['name'] }}</div>
                                                <small class="text-muted">{{ $user['email'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($user['role'] === 'Admin')
                                            <span class="badge bg-danger">ADMIN</span>
                                        @else
                                            <span class="badge bg-light text-dark border">USER</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user['status'] === 'Premium')
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-crown me-1"></i> PREMIUM
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">FREE</span>
                                        @endif
                                    </td>
                                    <td data-sort="{{ $user['last_active'] }}">
                                        @if($user['last_active'] > 0)
                                            {{ \Carbon\Carbon::createFromTimestampMs($user['last_active'])->diffForHumans() }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                {{-- DataTables handles empty state --}}
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
        $('#usersTable').DataTable({
            "pageLength": 5,
            "order": [[ 3, "desc" ]], // Sort by 'Last Active' descending
            "language": { "search": "Search users:" }
        });
    });

    // 2. User Status Chart
    new Chart(document.getElementById('userStatusChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($data['charts']['status']['labels']) !!},
            datasets: [{
                data: {!! json_encode($data['charts']['status']['data']) !!},
                backgroundColor: ['#ffc107', '#6c757d'], // Premium (Gold), Free (Gray)
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