@extends('layouts.app')

@section('title', $tournament['name'] ?? 'Tournament Bracket')

@section('content')
<div class="container-fluid py-5">
    
    {{-- PERMISSION CHECK --}}
    @php
        $currentUserUid = session('firebase_uid');
        $creatorUid = $tournament['creatorUid'] ?? null;
        $isCreator = $currentUserUid && $creatorUid && ($currentUserUid === $creatorUid);
        $isAdmin = Auth::check() && (Auth::user()->isAdmin ?? false);
        $canEdit = $isAdmin || $isCreator;
    @endphp

    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-primary rounded p-4 shadow text-white position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 opacity-10">
                    <i class="fas fa-trophy" style="font-size: 8rem;"></i>
                </div>
                
                <div class="d-flex justify-content-between align-items-start position-relative">
                    <div>
                        <h1 class="display-5 fw-bold">{{ $tournament['name'] ?? 'Tournament' }}</h1>
                        
                        <div class="d-flex gap-3 align-items-center mt-2">
                            <span class="badge bg-light text-primary fw-bold fs-6">
                                {{ count($tournament['teams'] ?? []) }} Teams
                            </span>
                            
                            <span class="badge {{ ($tournament['status'] ?? '') == 'completed' ? 'bg-success' : 'bg-warning text-dark' }} fw-bold fs-6">
                                {{ strtoupper($tournament['status'] ?? 'UPCOMING') }}
                            </span>
                            
                            @if(!empty($tournament['winner']))
                                <div class="ms-3 fw-bold text-warning fs-4">
                                    <i class="fas fa-crown me-2"></i>Winner: {{ $tournament['winner'] }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- DELETE BUTTON (SweetAlert Trigger) --}}
                    @if($canEdit)
                        <form id="delete-tournament-form" action="{{ route('tournaments.destroy', $tournament['id']) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger fw-bold shadow-sm" onclick="confirmDelete()">
                                <i class="fas fa-trash-alt me-2"></i>Delete Tournament
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-body bg-light p-0" style="overflow-x: auto;">
            <div class="bracket-container p-5 d-flex justify-content-center">
                
                @if(isset($rounds) && count($rounds) > 0)
                    @foreach($rounds as $roundNum => $matches)
                        <div class="round-column mx-4 d-flex flex-column justify-content-around">
                            <div class="text-center mb-4 fw-bold text-uppercase text-muted tracking-wider">
                                @if($loop->last) Final @elseif($loop->iteration == $loop->count - 1) Semi-Finals @else Round {{ $roundNum }} @endif
                            </div>
                            
                            @foreach($matches as $match)
                                <div class="match-card card border-0 shadow-sm mb-4" style="width: 250px;">
                                    <div class="match-team p-2 border-bottom d-flex justify-content-between align-items-center 
                                        {{ ($match['winner'] ?? null) === ($match['home'] ?? null) && ($match['home'] ?? null) != null ? 'bg-success text-white' : '' }}">
                                        
                                        <span class="fw-bold text-truncate" style="max-width: 160px;">
                                            {{ $match['home'] ?? 'TBD' }}
                                        </span>
                                        <span class="badge bg-light text-dark">{{ $match['scoreHome'] ?? 0 }}</span>
                                    </div>

                                    <div class="match-team p-2 d-flex justify-content-between align-items-center
                                        {{ ($match['winner'] ?? null) === ($match['away'] ?? null) && ($match['away'] ?? null) != null ? 'bg-success text-white' : '' }}">
                                        
                                        <span class="fw-bold text-truncate" style="max-width: 160px;">
                                            {{ $match['away'] ?? 'TBD' }}
                                        </span>
                                        <span class="badge bg-light text-dark">{{ $match['scoreAway'] ?? 0 }}</span>
                                    </div>

                                    @php
                                        $home = $match['home'] ?? null;
                                        $away = $match['away'] ?? null;
                                        $winner = $match['winner'] ?? null;
                                    @endphp

                                    @if($canEdit && $home && $away && !$winner)
                                        <button type="button" class="btn btn-sm btn-warning w-100 rounded-0" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#updateMatchModal{{ $match['id'] }}">
                                            <i class="fas fa-edit me-1"></i> Update Result
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        
                        @if(!$loop->last)
                            <div class="connector-column d-flex align-items-center" style="width: 40px;">
                                <i class="fas fa-chevron-right text-muted opacity-25 fa-2x"></i>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <h4 class="text-muted">Bracket generation pending...</h4>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODALS SECTION --}}
@if(isset($rounds) && $canEdit)
    @foreach($rounds as $roundNum => $matches)
        @foreach($matches as $match)
            @if(($match['home'] ?? null) && ($match['away'] ?? null))
            <div class="modal fade" id="updateMatchModal{{ $match['id'] }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <form action="{{ route('tournaments.match.update', [$tournament['id'], $match['id']]) }}" method="POST">
                            @csrf
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Update Match Result</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                
                                <div class="row align-items-center mb-4 g-2">
                                    <div class="col-5">
                                        <label class="form-label small text-muted fw-bold">Home Team</label>
                                        <input type="text" name="home_team_name" class="form-control fw-bold text-primary" value="{{ $match['home'] }}" required>
                                    </div>
                                    <div class="col-2 text-center pt-3 fw-bold text-muted">VS</div>
                                    <div class="col-5">
                                        <label class="form-label small text-muted fw-bold">Away Team</label>
                                        <input type="text" name="away_team_name" class="form-control fw-bold text-danger" value="{{ $match['away'] }}" required>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="card border-primary h-100 text-center p-3 selected-team-card" id="card_home_{{ $match['id'] }}">
                                            <span class="badge bg-primary mb-2">HOME</span>
                                            <input type="number" 
                                                   name="score_home" 
                                                   id="score_home_{{ $match['id'] }}"
                                                   class="form-control form-control-lg text-center fw-bold mb-2" 
                                                   required min="0" 
                                                   value="{{ $match['scoreHome'] ?? 0 }}"
                                                   oninput="checkWinner('{{ $match['id'] }}')">
                                            
                                            <div>
                                                <input type="radio" class="btn-check" name="winner" id="win_home_{{ $match['id'] }}" value="home" required onchange="highlightWinner('{{ $match['id'] }}', 'home')">
                                                <label class="btn btn-outline-primary w-100 btn-sm" for="win_home_{{ $match['id'] }}">Winner</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="card border-danger h-100 text-center p-3 selected-team-card" id="card_away_{{ $match['id'] }}">
                                            <span class="badge bg-danger mb-2">AWAY</span>
                                            <input type="number" 
                                                   name="score_away" 
                                                   id="score_away_{{ $match['id'] }}"
                                                   class="form-control form-control-lg text-center fw-bold mb-2" 
                                                   required min="0" 
                                                   value="{{ $match['scoreAway'] ?? 0 }}"
                                                   oninput="checkWinner('{{ $match['id'] }}')">
                                            
                                            <div>
                                                <input type="radio" class="btn-check" name="winner" id="win_away_{{ $match['id'] }}" value="away" required onchange="highlightWinner('{{ $match['id'] }}', 'away')">
                                                <label class="btn btn-outline-danger w-100 btn-sm" for="win_away_{{ $match['id'] }}">Winner</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success fw-bold px-4">Save & Advance</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    @endforeach
@endif

@push('styles')
<style>
    .bracket-container {
        min-width: 100%;
    }
    .match-card {
        transition: transform 0.2s;
    }
    .match-card:hover {
        transform: scale(1.02);
    }
    .round-column {
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        min-height: 600px; 
    }
    .selected-team-card {
        transition: all 0.2s;
    }
    input[value="home"]:checked ~ label {
        background-color: #0d6efd;
        color: white;
    }
    input[value="away"]:checked ~ label {
        background-color: #dc3545;
        color: white;
    }
</style>
@endpush

@push('scripts')
{{-- SWEET ALERT LIBRARY --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // 1. SweetAlert for DELETE Confirmation
    function confirmDelete() {
        Swal.fire({
            title: 'Delete Tournament?',
            text: "You won't be able to revert this! All match data will be lost.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-tournament-form').submit();
            }
        });
    }

    // 2. SweetAlert Toast for Success/Error messages from Server
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: "{{ session('success') }}",
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: "{{ session('error') }}",
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true
            });
        @endif
    });

    // 3. Winner Highlight Logic
    function checkWinner(matchId) {
        const homeScore = parseInt(document.getElementById('score_home_' + matchId).value) || 0;
        const awayScore = parseInt(document.getElementById('score_away_' + matchId).value) || 0;
        
        if (homeScore > awayScore) {
            document.getElementById('win_home_' + matchId).checked = true;
            highlightWinner(matchId, 'home');
        } else if (awayScore > homeScore) {
            document.getElementById('win_away_' + matchId).checked = true;
            highlightWinner(matchId, 'away');
        }
    }

    function highlightWinner(matchId, winner) {
        const homeCard = document.getElementById('card_home_' + matchId);
        const awayCard = document.getElementById('card_away_' + matchId);

        if (winner === 'home') {
            homeCard.classList.add('bg-light');
            awayCard.classList.remove('bg-light');
            awayCard.style.opacity = '0.6';
            homeCard.style.opacity = '1';
        } else {
            awayCard.classList.add('bg-light');
            homeCard.classList.remove('bg-light');
            homeCard.style.opacity = '0.6';
            awayCard.style.opacity = '1';
        }
    }
</script>
@endpush
@endsection