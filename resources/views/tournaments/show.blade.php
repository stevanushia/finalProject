@extends('layouts.app')

@section('title', $tournament['name'] ?? 'Tournament Bracket')

@section('content')
<div class="container-fluid py-5">
    
    {{-- HEADER SECTION --}}
    <div class="row mb-5">
        <div class="col-12">
            <div class="bg-primary rounded-3 p-4 shadow-lg text-white position-relative overflow-hidden">
                <div class="d-flex justify-content-between align-items-center position-relative z-1">
                    <h1 class="display-5 fw-bolder m-0">{{ $tournament['name'] ?? 'Tournament' }}</h1>
                    
                    @php
                        $currentUserUid = session('firebase_uid');
                        $creatorUid = $tournament['creatorUid'] ?? null;
                        $isCreator = $currentUserUid && $creatorUid && ($currentUserUid === $creatorUid);
                        $isAdmin = Auth::check() && (Auth::user()->isAdmin ?? false);
                        $canEdit = $isAdmin || $isCreator;
                    @endphp

                    @if($canEdit)
                        <form id="delete-tournament-form" action="{{ route('tournaments.destroy', $tournament['id']) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-danger bg-gradient shadow-sm fw-bold" onclick="confirmDelete()">
                                <i class="fas fa-trash-alt me-2"></i>Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- BRACKET SECTION --}}
    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-body bg-light p-0 overflow-auto custom-scrollbar">
            <div class="bracket-container p-5 d-flex flex-row">
                
                @if(isset($rounds) && count($rounds) > 0)
                    @foreach($rounds as $roundNum => $matches)
                        
                        @php
                            // SPACING CALCULATIONS
                            $roundIndex = $loop->index; 
                            $baseGap = 30; 
                            
                            // IMPORTANT: Match Height must include the button area
                            // Team 1 (40px) + Team 2 (40px) + Button (35px) + Borders/Padding approx = 130-140px
                            $matchHeight = $canEdit ? 140 : 100; 
                            
                            $verticalMargin = ($baseGap * pow(2, $roundIndex)) / 2; 
                        @endphp

                        {{-- ROUND COLUMN --}}
                        <div class="round-column d-flex flex-column justify-content-center" style="min-width: 260px;">
                            
                            @foreach($matches as $match)
                                <div class="match-wrapper d-flex align-items-center position-relative" style="margin-top: {{ $verticalMargin }}px; margin-bottom: {{ $verticalMargin }}px;">
                                    
                                    {{-- ROUND HEADER (Above first match only) --}}
                                    @if($loop->first)
                                        <div class="position-absolute w-100 text-center" style="top: -45px; left: 0;">
                                            <span class="badge bg-dark text-white px-4 py-2 rounded-pill text-uppercase tracking-widest shadow-sm border border-secondary" style="font-size: 0.8rem; letter-spacing: 2px;">
                                                @if($loop->parent->last) 🏆 Final 
                                                @elseif($loop->parent->iteration == $loop->parent->count - 1) Semi-Finals 
                                                @else Round {{ $roundNum }} 
                                                @endif
                                            </span>
                                        </div>
                                    @endif

                                    {{-- THE MATCH CARD --}}
                                    <div class="card border-0 shadow-sm w-100 overflow-hidden" style="height: {{ $matchHeight }}px;">
                                        
                                        {{-- 1. HOME TEAM --}}
                                        <div class="match-team px-3 border-bottom d-flex justify-content-between align-items-center flex-grow-1 
                                            {{ ($match['winner'] ?? null) === ($match['home'] ?? null) && ($match['home'] ?? null) != null ? 'bg-success bg-gradient text-white' : 'bg-white' }}"
                                            style="height: 35%;">
                                            
                                            <span class="fw-bold text-truncate" style="max-width: 140px; font-size: 0.9rem;">
                                                {{ $match['home'] ?? 'TBD' }}
                                            </span>
                                            <span class="badge {{ ($match['winner'] ?? null) === ($match['home'] ?? null) ? 'bg-white text-success' : 'bg-light text-dark' }}">{{ $match['scoreHome'] ?? 0 }}</span>
                                        </div>

                                        {{-- 2. AWAY TEAM --}}
                                        <div class="match-team px-3 d-flex justify-content-between align-items-center flex-grow-1
                                            {{ ($match['winner'] ?? null) === ($match['away'] ?? null) && ($match['away'] ?? null) != null ? 'bg-success bg-gradient text-white' : 'bg-white' }}"
                                            style="height: 35%;">
                                            
                                            <span class="fw-bold text-truncate" style="max-width: 140px; font-size: 0.9rem;">
                                                {{ $match['away'] ?? 'TBD' }}
                                            </span>
                                            <span class="badge {{ ($match['winner'] ?? null) === ($match['away'] ?? null) ? 'bg-white text-success' : 'bg-light text-dark' }}">{{ $match['scoreAway'] ?? 0 }}</span>
                                        </div>

                                        {{-- 3. ACTION BUTTON (FOOTER) --}}
                                        @php
                                            $home = $match['home'] ?? null;
                                            $away = $match['away'] ?? null;
                                            $winner = $match['winner'] ?? null;
                                        @endphp
                                        
                                        @if($canEdit)
                                            <div class="d-grid" style="height: 30%;">
                                                @if($home && $away && !$winner)
                                                    <button type="button" 
                                                            class="btn btn-warning rounded-0 fw-bold d-flex align-items-center justify-content-center gap-2"
                                                            style="font-size: 0.8rem; border-top: 1px solid rgba(0,0,0,0.1);"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#updateMatchModal{{ $match['id'] }}">
                                                        <i class="fas fa-pen"></i> Update Result
                                                    </button>
                                                @elseif($winner)
                                                     <div class="bg-light text-muted d-flex align-items-center justify-content-center small border-top" style="font-size: 0.75rem;">
                                                        <i class="fas fa-check-circle me-1"></i> Completed
                                                     </div>
                                                @else
                                                    <div class="bg-light text-muted d-flex align-items-center justify-content-center small border-top" style="font-size: 0.75rem;">
                                                        Waiting for teams...
                                                     </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    
                                    {{-- CONNECTOR LINE (Horizontal exiting match) --}}
                                    @if(!$loop->parent->last)
                                        <div class="bracket-line-h" style="width: 40px; height: 2px; background-color: #dee2e6;"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        
                        {{-- CONNECTOR COLUMN --}}
                        @if(!$loop->last)
                            <div class="connector-column d-flex flex-column justify-content-center" style="width: 40px;">
                                @php
                                    $connectorCount = count($matches) / 2; 
                                    // Use the same match height calculated above for alignment
                                    $connectorHeight = $matchHeight + ($verticalMargin * 2); 
                                @endphp

                                @for($i = 0; $i < $connectorCount; $i++)
                                    <div class="bracket-connector border-end border-2 border-secondary border-opacity-25" 
                                         style="height: {{ $connectorHeight }}px; width: 50%; margin-top: {{ $verticalMargin }}px; margin-bottom: {{ $verticalMargin }}px; border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;">
                                    </div>
                                    @if($i < $connectorCount - 1)
                                        <div style="height: {{ $connectorHeight }}px;"></div> 
                                    @endif
                                @endfor
                            </div>
                            
                            {{-- Spacer for next round entry --}}
                            <div class="d-flex flex-column justify-content-center">
                                 @for($i = 0; $i < count($matches)/2; $i++)
                                    <div style="height: {{ ($matchHeight + ($verticalMargin * 2)) * 2 }}px; display: flex; align-items: center;">
                                        <div style="width: 20px; height: 2px; background-color: #dee2e6;"></div>
                                    </div>
                                 @endfor
                            </div>
                        @endif

                    @endforeach
                @else
                    <div class="text-center py-5 w-100">
                        <h4 class="text-muted fw-light">Bracket generation pending...</h4>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODALS (Kept the same) --}}
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
                                        <label class="form-label small text-muted fw-bold">Home</label>
                                        <input type="text" name="home_team_name" class="form-control fw-bold" value="{{ $match['home'] }}" readonly>
                                    </div>
                                    <div class="col-2 text-center pt-3 fw-bold">VS</div>
                                    <div class="col-5">
                                        <label class="form-label small text-muted fw-bold">Away</label>
                                        <input type="text" name="away_team_name" class="form-control fw-bold" value="{{ $match['away'] }}" readonly>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="card border-primary h-100 text-center p-3 selected-team-card" id="card_home_{{ $match['id'] }}">
                                            <span class="badge bg-primary mb-2">HOME</span>
                                            <input type="number" name="score_home" id="score_home_{{ $match['id'] }}" class="form-control form-control-lg text-center fw-bold mb-2" required min="0" value="{{ $match['scoreHome'] ?? 0 }}" oninput="checkWinner('{{ $match['id'] }}')">
                                            <div>
                                                <input type="radio" class="btn-check" name="winner" id="win_home_{{ $match['id'] }}" value="home" required onchange="highlightWinner('{{ $match['id'] }}', 'home')">
                                                <label class="btn btn-outline-primary w-100 btn-sm" for="win_home_{{ $match['id'] }}">Winner</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card border-danger h-100 text-center p-3 selected-team-card" id="card_away_{{ $match['id'] }}">
                                            <span class="badge bg-danger mb-2">AWAY</span>
                                            <input type="number" name="score_away" id="score_away_{{ $match['id'] }}" class="form-control form-control-lg text-center fw-bold mb-2" required min="0" value="{{ $match['scoreAway'] ?? 0 }}" oninput="checkWinner('{{ $match['id'] }}')">
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
                                <button type="submit" class="btn btn-success fw-bold px-4">Save</button>
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
    .custom-scrollbar::-webkit-scrollbar { height: 8px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #aaa; }
    .match-team { transition: background-color 0.2s; }
    .selected-team-card { transition: all 0.2s; }
    input[value="home"]:checked ~ label { background-color: #0d6efd; color: white; }
    input[value="away"]:checked ~ label { background-color: #dc3545; color: white; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // SweetAlert and Logic Scripts
    function confirmDelete() {
        Swal.fire({
            title: 'Delete Tournament?', text: "Cannot be reverted!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!'
        }).then((result) => { if (result.isConfirmed) document.getElementById('delete-tournament-form').submit(); });
    }
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success')) Swal.fire({ icon: 'success', title: 'Success', text: "{{ session('success') }}", toast: true, position: 'top-end', timer: 3000, showConfirmButton: false }); @endif
        @if(session('error')) Swal.fire({ icon: 'error', title: 'Error', text: "{{ session('error') }}", toast: true, position: 'top-end', timer: 4000, showConfirmButton: false }); @endif
    });
    function checkWinner(id) {
        const h = parseInt(document.getElementById('score_home_'+id).value)||0;
        const a = parseInt(document.getElementById('score_away_'+id).value)||0;
        if(h>a) { document.getElementById('win_home_'+id).checked=true; highlightWinner(id,'home'); }
        else if(a>h) { document.getElementById('win_away_'+id).checked=true; highlightWinner(id,'away'); }
    }
    function highlightWinner(id, w) {
        const hc = document.getElementById('card_home_'+id);
        const ac = document.getElementById('card_away_'+id);
        if(w==='home'){ hc.classList.add('bg-light'); ac.classList.remove('bg-light'); ac.style.opacity='0.5'; hc.style.opacity='1'; }
        else { ac.classList.add('bg-light'); hc.classList.remove('bg-light'); hc.style.opacity='0.5'; ac.style.opacity='1'; }
    }
</script>
@endpush
@endsection