@extends('layouts.app')

@section('title', 'My Teams')

@section('content')

<div class="container py-5">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="display-5 fw-bold text-primary">My Teams</h1>
            <p class="text-muted">Manage your team rosters and view tournament history.</p>
        </div>
        <div class="col-auto">
            {{-- Optional: Link to create tournament since that's where teams are made --}}
            <a href="{{ route('tournaments.create') }}" class="btn btn-success fw-bold shadow-sm">
                <i class="fas fa-plus me-2"></i>New Tournament
            </a>
        </div>
    </div>

    @if(empty($teams))
        <div class="card border-0 shadow-sm rounded-3 py-5">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-users-slash fa-4x text-muted opacity-25"></i>
                </div>
                <h4 class="text-muted">No Teams Found</h4>
                <p class="text-muted mb-4">You haven't created any teams yet. Create a tournament to get started!</p>
                <a href="{{ route('tournaments.create') }}" class="btn btn-primary">Create Tournament</a>
            </div>
        </div>
    @else
        <div class="row g-4">
            @foreach($teams as $id => $team)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm hover-card transition-all">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="bg-light rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="fas fa-shield-alt fa-2x text-primary"></i>
                                </div>
                                <span class="badge bg-light text-muted border">
                                    {{ isset($team['createdAt']) ? date('M Y', $team['createdAt']/1000) : 'N/A' }}
                                </span>
                            </div>
                            
                            <h4 class="card-title fw-bold mb-2 text-dark">{{ $team['name'] }}</h4>
                            
                            @php
                                $rosterCount = isset($team['defaultRoster']) ? count($team['defaultRoster']) : 0;
                            @endphp
                            <p class="card-text text-muted mb-4">
                                <i class="fas fa-user-friends me-2"></i>{{ $rosterCount }} Players registered
                            </p>
                            
                            <a href="{{ route('teams.show', $id) }}" class="btn btn-outline-primary w-100 fw-bold stretched-link">
                                View History <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('styles')
<style>
    .hover-card { transition: transform 0.2s, box-shadow 0.2s; }
    .hover-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
</style>
@endpush
@endsection