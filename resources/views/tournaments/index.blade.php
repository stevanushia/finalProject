@extends('layouts.app')

@section('title', 'Tournaments')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-primary rounded p-4 shadow">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="display-5 mb-2 fw-bold text-white">Tournaments</h1>
                        <p class="lead mb-0 text-white">Manage brackets and competition results</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="d-flex justify-content-end align-items-center gap-3">
                            <div class="btn-group" role="group" aria-label="Filter tournaments">
                                <button type="button" class="btn btn-light fw-bold active" data-filter="all">All</button>
                                <button type="button" class="btn btn-light fw-bold" data-filter="in-progress">In Progress</button>
                                <button type="button" class="btn btn-light fw-bold" data-filter="completed">Completed</button>
                            </div>

                            <a href="{{ route('tournaments.create') }}" class="btn btn-warning fw-bold shadow-sm">
                                <i class="fas fa-plus me-2"></i>Create
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4" id="tournament-list">
        @forelse($tournaments as $id => $t)
            @php
                // Map database status to filter status
                // If 'completed', use 'completed'. Everything else (upcoming, ongoing) is 'in-progress'
                $dbStatus = $t['status'] ?? 'upcoming';
                $filterStatus = $dbStatus === 'completed' ? 'completed' : 'in-progress';
                
                $badgeClass = $dbStatus === 'completed' ? 'bg-success' : 'bg-warning text-dark';
                $cardBorder = $dbStatus === 'completed' ? 'border-success' : 'border-primary';
                $headerClass = $dbStatus === 'completed' ? 'bg-success' : 'bg-primary';
            @endphp

            <div class="col-md-6 col-lg-4 tournament-card" data-status="{{ $filterStatus }}">
                <div class="card h-100 shadow {{ $cardBorder }}">
                    <div class="card-header {{ $headerClass }} text-white py-3">
                        <h5 class="card-title mb-0 fw-bold text-truncate">{{ $t['name'] }}</h5>
                    </div>
                    <div class="card-body bg-light">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="badge bg-white text-dark border">{{ $t['participantCount'] }} Teams</span>
                            <span class="badge {{ $badgeClass }}">
                                {{ strtoupper($dbStatus) }}
                            </span>
                        </div>
                        
                        @if(!empty($t['winner']))
                            <div class="mb-3 p-2 bg-white rounded border text-center">
                                <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">Champion</small>
                                <div class="fw-bold text-success">
                                    <i class="fas fa-crown me-1"></i> {{ $t['winner'] }}
                                </div>
                            </div>
                        @else
                            <div class="mb-3 p-2 bg-white rounded border text-center">
                                <small class="text-muted d-block">Est. Start Date</small>
                                <div class="fw-bold text-dark">
                                    {{-- SAFE DATE CHECK --}}
                                    @if(!empty($t['startDate']))
                                        {{ \Carbon\Carbon::createFromTimestampMs($t['startDate'])->format('M d, Y') }}
                                    @else
                                        <span class="text-muted fst-italic">TBD</span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <p class="text-muted small mb-0 text-end">
                            <i class="fas fa-clock me-1"></i> Created: 
                            @if(!empty($t['createdAt']))
                                {{-- Use the actual creation time --}}
                                {{ \Carbon\Carbon::createFromTimestampMs($t['createdAt'])->diffForHumans() }}
                            @else
                                {{-- Fallback for old data that didn't have this field --}}
                                Recently
                            @endif
                        </p>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0 pb-3">
                        <a href="{{ route('tournaments.show', $id) }}" class="btn btn-outline-dark w-100 fw-bold mt-3">
                            View Bracket <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="text-muted opacity-50 mb-3">
                    <i class="fas fa-trophy fa-4x"></i>
                </div>
                <h3>No tournaments found</h3>
                <p class="text-muted">Create your first tournament to get started!</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
    .tournament-card {
        transition: transform 0.2s ease-in-out;
    }

    .tournament-card:hover {
        transform: translateY(-5px);
    }

    .btn-group .btn {
        border-radius: 0;
        margin: 0 1px;
        transition: all 0.2s;
    }
    
    .btn-group .btn:first-child {
        border-top-left-radius: 0.375rem;
        border-bottom-left-radius: 0.375rem;
    }
    
    .btn-group .btn:last-child {
        border-top-right-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
    }

    /* Using !important to override Bootstrap utility conflicts if necessary */
    .btn-group .btn.active {
        background-color: #28a745 !important; /* Green active state */
        color: white !important;
        border-color: #28a745 !important;
    }

    .btn-group .btn:hover {
        background-color: #e2e6ea;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterButtons = document.querySelectorAll('.btn-group .btn');
        const tournamentCards = document.querySelectorAll('.tournament-card');

        filterButtons.forEach(button => {
            button.addEventListener('click', function () {
                // 1. Reset all buttons styles
                filterButtons.forEach(btn => {
                    btn.classList.remove('active', 'btn-success', 'text-white');
                    btn.classList.add('btn-light', 'text-dark');
                });

                // 2. Activate clicked button
                this.classList.add('active', 'btn-success', 'text-white');
                this.classList.remove('btn-light', 'text-dark');

                // 3. Filter Logic
                const filter = this.getAttribute('data-filter');

                tournamentCards.forEach(card => {
                    const status = card.getAttribute('data-status');
                    
                    if (filter === 'all') {
                        card.style.display = 'block'; // Show all
                    } else if (filter === status) {
                        card.style.display = 'block'; // Show matching
                    } else {
                        card.style.display = 'none';  // Hide non-matching
                    }
                });
            });
        });
    });
</script>
@endpush