@extends('layouts.app')

@section('title', $tournament['name'] ?? 'Tournament Bracket')

@section('content')
<div class="container-fluid py-5">
    
    {{-- HEADER SECTION --}}
    <div class="row mb-5">
        <div class="col-12">
            <div class="bg-primary rounded-3 p-4 shadow-lg text-white position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 opacity-10">
                    <i class="fas fa-trophy" style="font-size: 8rem;"></i>
                </div>
                
                <div class="d-flex justify-content-between align-items-center position-relative z-1">
                    <div>
                        <h1 class="display-5 fw-bolder m-0">{{ $tournament['name'] ?? 'Tournament' }}</h1>
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
                    
                    {{-- PERMISSION LOGIC --}}
                    @php
                        $currentUserUid = session('firebase_uid');
                        $creatorUid = $tournament['creatorUid'] ?? null;
                        
                        // 1. Check if Owner
                        $isCreator = $currentUserUid && $creatorUid && ($currentUserUid === $creatorUid);
                        
                        // 2. Check if Admin (Check Model OR Session)
                        $isAdmin = false;
                        if (Auth::check()) {
                            $isAdmin = (Auth::user()->isAdmin ?? false) || (session('firebase_is_admin') === true);
                        }

                        // 3. Final Permission
                        $canEdit = $isAdmin || $isCreator;
                    @endphp

                    {{-- DELETE BUTTON (Visible if Admin OR Creator) --}}
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
            <div class="bracket-container p-5 d-flex flex-row justify-content-center">
                
                @if(isset($rounds) && count($rounds) > 0)
                    @foreach($rounds as $roundNum => $matches)
                        
                        @php
                            $roundIndex = $loop->index; 
                            $baseGap = 30; 
                            $matchHeight = $canEdit ? 140 : 100; // Taller cards for Editors
                            $verticalMargin = ($baseGap * pow(2, $roundIndex)) / 2; 
                        @endphp

                        {{-- ROUND COLUMN --}}
                        <div class="round-column d-flex flex-column justify-content-center" style="min-width: 280px; margin: 0 20px;">
                            
                            <div class="text-center mb-4 fw-bold text-uppercase text-muted tracking-wider">
                                @if($loop->last) Final @elseif($loop->iteration == $loop->count - 1) Semi-Finals @else Round {{ $roundNum }} @endif
                            </div>

                            @foreach($matches as $match)
                                <div class="match-wrapper d-flex align-items-center position-relative" style="margin-top: {{ $verticalMargin }}px; margin-bottom: {{ $verticalMargin }}px;">
                                    
                                    {{-- THE MATCH CARD --}}
                                    <div class="card border-0 shadow-sm w-100 overflow-hidden match-card" style="height: {{ $matchHeight }}px;">
                                        
                                        {{-- 1. HOME TEAM --}}
                                        <div class="match-team px-3 border-bottom d-flex align-items-center justify-content-between 
                                            {{ ($match['winner'] ?? null) === ($match['home'] ?? null) && ($match['home'] ?? null) != null ? 'bg-success bg-gradient text-white' : 'bg-white' }}"
                                            style="height: 35%;">
                                            
                                            {{-- NAME CONTAINER (Flex-Grow to take space, Min-Width 0 to allow truncation) --}}
                                            <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
                                                <span class="fw-bold text-truncate" title="{{ $match['home'] ?? 'TBD' }}">
                                                    {{ $match['home'] ?? 'TBD' }}
                                                </span>
                                                
                                                {{-- EDIT BUTTON (Only show if Team exists) --}}
                                                @if(($match['home'] ?? null) && ($match['home'] ?? null) !== 'TBD')
                                                    <span class="ms-2 flex-shrink-0"> {{-- flex-shrink-0 prevents crushing --}}
                                                        @if($canEdit)
                                                            <button type="button" class="btn btn-link p-0 text-primary" onclick="editRoster('{{ $match['home'] }}')" title="Edit Roster" style="line-height: 1;">
                                                                <i class="fas fa-edit"></i> {{-- Changed icon to fa-edit (standard) --}}
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-link p-0 text-muted" onclick="viewRoster('{{ $match['home'] }}')" title="View Roster" style="line-height: 1;">
                                                                <i class="fas fa-info-circle"></i>
                                                            </button>
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- SCORE BADGE --}}
                                            <span class="badge ms-2 {{ ($match['winner'] ?? null) === ($match['home'] ?? null) ? 'bg-white text-success' : 'bg-light text-dark' }} flex-shrink-0">
                                                {{ $match['scoreHome'] ?? 0 }}
                                            </span>
                                        </div>

                                        {{-- 2. AWAY TEAM --}}
                                        <div class="match-team px-3 d-flex align-items-center justify-content-between
                                            {{ ($match['winner'] ?? null) === ($match['away'] ?? null) && ($match['away'] ?? null) != null ? 'bg-success bg-gradient text-white' : 'bg-white' }}"
                                            style="height: 35%;">
                                            
                                            {{-- NAME CONTAINER --}}
                                            <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
                                                <span class="fw-bold text-truncate" title="{{ $match['away'] ?? 'TBD' }}">
                                                    {{ $match['away'] ?? 'TBD' }}
                                                </span>
                                                
                                                {{-- EDIT BUTTON --}}
                                                @if(($match['away'] ?? null) && ($match['away'] ?? null) !== 'TBD')
                                                    <span class="ms-2 flex-shrink-0">
                                                        @if($canEdit)
                                                            <button type="button" class="btn btn-link p-0 text-primary" onclick="editRoster('{{ $match['away'] }}')" title="Edit Roster" style="line-height: 1;">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-link p-0 text-muted" onclick="viewRoster('{{ $match['away'] }}')" title="View Roster" style="line-height: 1;">
                                                                <i class="fas fa-info-circle"></i>
                                                            </button>
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- SCORE BADGE --}}
                                            <span class="badge ms-2 {{ ($match['winner'] ?? null) === ($match['away'] ?? null) ? 'bg-white text-success' : 'bg-light text-dark' }} flex-shrink-0">
                                                {{ $match['scoreAway'] ?? 0 }}
                                            </span>
                                        </div>

                                        {{-- 3. UPDATE RESULT BUTTON --}}
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
                                    
                                    {{-- HORIZONTAL LINE --}}
                                    @if(!$loop->parent->last)
                                        <div class="bracket-line-h" style="width: 40px; height: 2px; background-color: #dee2e6; position: absolute; right: -40px;"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        
                        {{-- VERTICAL CONNECTORS --}}
                        @if(!$loop->last)
                            <div class="connector-column d-flex flex-column justify-content-center" style="width: 40px;">
                                @php
                                    $connectorCount = count($matches) / 2; 
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

{{-- 1. UPDATE MATCH MODALS (Score) --}}
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
                                        <div class="card border-primary h-100 text-center p-3" id="card_home_{{ $match['id'] }}">
                                            <span class="badge bg-primary mb-2">HOME</span>
                                            <input type="number" name="score_home" id="score_home_{{ $match['id'] }}" class="form-control form-control-lg text-center fw-bold mb-2" required min="0" value="{{ $match['scoreHome'] ?? 0 }}" oninput="checkWinner('{{ $match['id'] }}')">
                                            <div>
                                                <input type="radio" class="btn-check" name="winner" id="win_home_{{ $match['id'] }}" value="home" required onchange="highlightWinner('{{ $match['id'] }}', 'home')">
                                                <label class="btn btn-outline-primary w-100 btn-sm" for="win_home_{{ $match['id'] }}">Winner</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card border-danger h-100 text-center p-3" id="card_away_{{ $match['id'] }}">
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

{{-- 2. ROSTER VIEW MODAL (For Guests) --}}
<div class="modal fade" id="rosterViewModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title fw-bold" style="color: white" id="rosterTeamName">Team Roster</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <ul class="list-group list-group-flush" id="rosterList"></ul>
            </div>
        </div>
    </div>
</div>

{{-- 3. ROSTER EDIT MODAL (For Admin/Creator) --}}
@if($canEdit)
<div class="modal fade" id="rosterEditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('tournaments.team.update', $tournament['id']) }}" method="POST">
                @csrf
                <input type="hidden" name="team_key" id="editTeamKey">
                <input type="hidden" name="players" id="editPlayersJson">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2"></i>Edit Team & Roster</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Team Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-shield-alt"></i></span>
                            <input type="text" name="new_name" id="editTeamName" class="form-control fw-bold text-primary">
                        </div>
                        <div class="form-text text-muted">Changing name here updates the roster, not match history.</div>
                    </div>
                    
                    <hr>
                    <h6 class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                        <span>Players</span>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addEditRow()">
                            <i class="fas fa-plus-circle"></i> Add Player
                        </button>
                    </h6>
                    
                    <table class="table table-bordered">
                        <thead class="table-light small">
                            <tr>
                                <th>Name</th>
                                <th width="20%">Number</th>
                                <th width="20%">Pos</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody id="editRosterTableBody"></tbody>
                    </table>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold" onclick="saveEditRosterData()">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar { height: 8px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #aaa; }
    .match-team { transition: background-color 0.2s; }
    .match-card { transition: transform 0.2s; }
    .match-card:hover { transform: scale(1.02); z-index: 10; }
    .selected-team-card { transition: all 0.2s; }
    input[value="home"]:checked ~ label { background-color: #0d6efd; color: white; }
    input[value="away"]:checked ~ label { background-color: #dc3545; color: white; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const allTeamsData = {!! json_encode($tournament['teams'] ?? []) !!};
    let editPlayers = [];

    // --- VIEW ROSTER (Guests) ---
    function viewRoster(teamName) {
        document.getElementById('rosterTeamName').innerText = teamName;
        const list = document.getElementById('rosterList');
        list.innerHTML = '';

        let teamData = Array.isArray(allTeamsData) ? null : allTeamsData[teamName];

        if (teamData && teamData.players && teamData.players.length > 0) {
            teamData.players.forEach(p => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.innerHTML = `
                    <div><span class="fw-bold text-primary me-2">#${p.number}</span> ${p.name}</div>
                    <span class="badge bg-light text-dark border">${p.pos}</span>
                `;
                list.appendChild(li);
            });
        } else {
            list.innerHTML = '<li class="list-group-item text-muted text-center py-3">No players registered.</li>';
        }
        new bootstrap.Modal(document.getElementById('rosterViewModal')).show();
    }

    // --- EDIT ROSTER (Admin/Creator) ---
    @if($canEdit)
    function editRoster(teamName) {
        // 1. Setup Form Keys
        document.getElementById('editTeamKey').value = teamName;
        document.getElementById('editTeamName').value = teamName;
        
        // 2. Get Players
        let teamData = Array.isArray(allTeamsData) ? null : allTeamsData[teamName];
        editPlayers = (teamData && teamData.players) ? JSON.parse(JSON.stringify(teamData.players)) : [];
        
        // 3. Render & Show
        renderEditRows();
        new bootstrap.Modal(document.getElementById('rosterEditModal')).show();
    }

    function renderEditRows() {
        const tbody = document.getElementById('editRosterTableBody');
        tbody.innerHTML = '';
        editPlayers.forEach((p, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" class="form-control form-control-sm" value="${p.name}" onchange="updateEditPlayer(${index}, 'name', this.value)"></td>
                <td><input type="number" class="form-control form-control-sm" value="${p.number}" onchange="updateEditPlayer(${index}, 'number', this.value)"></td>
                <td>
                    <select class="form-select form-select-sm" onchange="updateEditPlayer(${index}, 'pos', this.value)">
                        <option value="PG" ${p.pos==='PG'?'selected':''}>PG</option>
                        <option value="SG" ${p.pos==='SG'?'selected':''}>SG</option>
                        <option value="SF" ${p.pos==='SF'?'selected':''}>SF</option>
                        <option value="PF" ${p.pos==='PF'?'selected':''}>PF</option>
                        <option value="C" ${p.pos==='C'?'selected':''}>C</option>
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm text-danger" onclick="removeEditPlayer(${index})"><i class="fas fa-times"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function addEditRow() { editPlayers.push({name: '', number: '', pos: 'PG'}); renderEditRows(); }
    function removeEditPlayer(index) { editPlayers.splice(index, 1); renderEditRows(); }
    function updateEditPlayer(index, field, value) { editPlayers[index][field] = value; }
    function saveEditRosterData() { 
        const clean = editPlayers.filter(p => p.name.trim() !== '');
        document.getElementById('editPlayersJson').value = JSON.stringify(clean);
    }
    @endif

    // --- GENERAL UTILS ---
    function confirmDelete() {
        Swal.fire({
            title: 'Delete Tournament?', text: "This action cannot be undone!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!'
        }).then((result) => { if (result.isConfirmed) document.getElementById('delete-tournament-form').submit(); });
    }

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

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success')) Swal.fire({ icon: 'success', title: 'Success', text: "{{ session('success') }}", toast: true, position: 'top-end', timer: 3000, showConfirmButton: false }); @endif
        @if(session('error')) Swal.fire({ icon: 'error', title: 'Error', text: "{{ session('error') }}", toast: true, position: 'top-end', timer: 4000, showConfirmButton: false }); @endif
    });
</script>
@endpush
@endsection