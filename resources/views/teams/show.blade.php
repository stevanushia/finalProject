@extends('layouts.app')

@section('title', $team['name'] . ' - History')

@section('content')
<div class="container py-5">
    
    {{-- Header Section --}}
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary text-white overflow-hidden">
                <div class="card-body p-5 position-relative">
                    <div class="position-absolute top-0 end-0 opacity-10 p-3">
                        <i class="fas fa-trophy" style="font-size: 10rem; transform: rotate(-15deg);"></i>
                    </div>
                    
                    <div class="row align-items-center position-relative z-1">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-3">
                                <a href="{{ route('teams.index') }}" class="btn btn-sm btn-light bg-opacity-25 text-black border-0 me-3">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                                <span class="badge bg-warning text-dark fw-bold">MASTER TEAM</span>
                            </div>
                            <h1 class="display-4 fw-bold mb-2">{{ $team['name'] }}</h1>
                            <p class="lead opacity-75 mb-0">Team History & Performance Log</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-4 mt-md-0">
                            <div class="d-inline-block text-center bg-white bg-opacity-10 rounded p-3 me-2">
                                <div class="h2 fw-bold mb-0" style="color: black">{{ $stats['played'] }}</div>
                                <div class="small text-uppercase opacity-75" style="color: black">Tournaments</div>
                            </div>
                            <div class="d-inline-block text-center bg-white bg-opacity-10 rounded p-3">
                                <div class="h2 fw-bold mb-0" style="color: black">{{ $stats['won'] }}</div>
                                <div class="small text-uppercase opacity-75" style="color: black">Championships</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Left Column: Default Roster --}}
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-users me-2 text-primary"></i>Current Roster</h5>
                </div>
                <div class="card-body p-0">
                    @if(!empty($team['defaultRoster']))
                        <ul class="list-group list-group-flush">
                            @foreach($team['defaultRoster'] as $player)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                    <div>
                                        <span class="fw-bold text-dark">{{ $player['name'] }}</span>
                                        <span class="text-muted small d-block">#{{ $player['number'] ?? '0' }}</span>
                                    </div>
                                    <span class="badge bg-light text-dark border">{{ $player['pos'] ?? '-' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-5">
                            <p class="text-muted">No default roster saved.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column: Tournament History --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-2 text-primary"></i>Tournament Log</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Tournament</th>
                                    <th>Result</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $index => $record)
                                    {{-- Main Row --}}
                                    <tr class="cursor-pointer" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="false">
                                        <td class="ps-4 text-muted">
                                            {{ \Carbon\Carbon::createFromTimestampMs($record['date'])->format('M d, Y') }}
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary">
                                                {{ $record['tournamentName'] }}
                                                @if(count($record['matches']) > 0)
                                                    <span class="badge bg-light text-secondary border ms-2 small">
                                                        <i class="fas fa-list-ul me-1"></i> {{ count($record['matches']) }} Games
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($record['result'] === 'Champion')
                                                <span class="badge bg-warning text-dark"><i class="fas fa-crown me-1"></i> Champion</span>
                                            @elseif($record['result'] === 'Participant')
                                                <span class="badge bg-info text-dark">Active</span>
                                            @else
                                                <span class="badge bg-light text-secondary border">Eliminated</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-light border">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    {{-- Expandable Details Row --}}
                                    <tr>
                                        <td colspan="4" class="p-0 border-0">
                                            <div class="collapse bg-light" id="collapse{{ $index }}">
                                                <div class="p-3 ps-5">
                                                    @if(count($record['matches']) > 0)
                                                        <h6 class="text-muted fw-bold small mb-2 text-uppercase">Match Performance</h6>
                                                        <div class="list-group shadow-sm">
                                                            @foreach($record['matches'] as $match)
                                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <span class="badge bg-secondary me-2">Round {{ $match['round'] }}</span>
                                                                        <span class="text-dark">vs <strong>{{ $match['opponent'] }}</strong></span>
                                                                    </div>
                                                                    
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="fw-bold me-3 {{ $match['resultColor'] === 'success' ? 'text-success' : ($match['resultColor'] === 'danger' ? 'text-danger' : 'text-muted') }}">
                                                                            {{ $match['result'] }} ({{ $match['score'] }})
                                                                        </span>
                                                                        
                                                                        {{-- LINK TO GAME OVERVIEW --}}
                                                                        @if($match['status'] === 'completed')
                                                                            <a href="{{ route('game.overview', ['gameId' => $match['matchId']]) }}" 
                                                                               class="btn btn-xs btn-outline-primary fw-bold"
                                                                               title="View Game Stats">
                                                                                <i class="fas fa-chart-bar me-1"></i> Stats
                                                                            </a>
                                                                        @else
                                                                            <span class="badge bg-light text-muted border">No Stats</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <p class="text-muted small mb-0 fst-italic">No matches played yet.</p>
                                                    @endif
                                                    
                                                    <div class="mt-3 text-end">
                                                        <a href="{{ route('tournaments.show', $record['id']) }}" class="btn btn-sm btn-link text-decoration-none">
                                                            View Full Bracket &rarr;
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            This team has not participated in any tournaments yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection