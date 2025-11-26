@extends('layouts.admin')

@section('title', 'Edit Tournament')
@section('header', 'Edit Tournament')

@section('content')
<div class="row g-4">
    
    {{-- 1. GENERAL SETTINGS --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h6 class="fw-bold m-0"><i class="fas fa-cog me-2"></i>General Settings</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.tournaments.update', $id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tournament Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $tournament['name'] ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Start Date</label>
                        <input type="date" name="start_date" class="form-control" 
                               value="{{ isset($tournament['startDate']) ? \Carbon\Carbon::createFromTimestampMs($tournament['startDate'])->format('Y-m-d') : '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Status</label>
                        <select name="status" class="form-select">
                            <option value="upcoming" {{ ($tournament['status']??'') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="ongoing" {{ ($tournament['status']??'') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ ($tournament['status']??'') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success fw-bold">Save Changes</button>
                        <a href="{{ route('tournaments.show', $id) }}" target="_blank" class="btn btn-outline-dark mt-2">
                            <i class="fas fa-sitemap me-2"></i> Manage Bracket & Scores
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. TEAM & ROSTER MANAGEMENT --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold m-0"><i class="fas fa-users me-2"></i>Participating Teams</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @php
                        $teams = $tournament['teams'] ?? [];
                        // Convert to array if it's not (legacy support)
                        if (!is_array($teams)) $teams = [];
                    @endphp

                    @foreach($teams as $key => $teamData)
                        @php
                            // Handle legacy format where teams might just be a list of names
                            $teamName = is_array($teamData) ? ($teamData['name'] ?? $key) : $teamData;
                            $players = is_array($teamData) ? ($teamData['players'] ?? []) : [];
                        @endphp
                        
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 fw-bold">{{ $teamName }}</h6>
                                    <small class="text-muted">{{ count($players) }} Players Registered</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary" 
                                        onclick='openRosterModal(@json($key), @json($teamName), @json($players))'>
                                    <i class="fas fa-user-edit me-1"></i> Edit Roster
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ROSTER EDIT MODAL --}}
<div class="modal fade" id="rosterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.tournaments.team.update', $id) }}" method="POST">
                @csrf
                <input type="hidden" name="team_key" id="modalTeamKey">
                <input type="hidden" name="players" id="modalPlayersJson">

                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Edit Team</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Team Name</label>
                        <input type="text" name="new_name" id="modalTeamName" class="form-control fw-bold">
                        <div class="form-text text-warning"><i class="fas fa-exclamation-triangle"></i> Changing the name here will NOT update the bracket history automatically.</div>
                    </div>
                    
                    <hr>
                    <h6 class="fw-bold mb-3">Player Roster</h6>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th width="20%">Number</th>
                                <th width="20%">Pos</th>
                                <th width="10%"></th>
                            </tr>
                        </thead>
                        <tbody id="rosterTableBody">
                            {{-- JS will fill this --}}
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="addPlayerRow()">
                        <i class="fas fa-plus"></i> Add Player
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" onclick="saveRosterData()">Save Roster</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let currentPlayers = [];

    function openRosterModal(key, name, players) {
        document.getElementById('modalTeamKey').value = key;
        document.getElementById('modalTeamName').value = name;
        currentPlayers = players || [];
        
        renderRows();
        new bootstrap.Modal(document.getElementById('rosterModal')).show();
    }

    function renderRows() {
        const tbody = document.getElementById('rosterTableBody');
        tbody.innerHTML = '';

        currentPlayers.forEach((p, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" class="form-control form-control-sm" value="${p.name}" onchange="updatePlayer(${index}, 'name', this.value)"></td>
                <td><input type="number" class="form-control form-control-sm" value="${p.number}" onchange="updatePlayer(${index}, 'number', this.value)"></td>
                <td>
                    <select class="form-select form-select-sm" onchange="updatePlayer(${index}, 'pos', this.value)">
                        <option value="PG" ${p.pos==='PG'?'selected':''}>PG</option>
                        <option value="SG" ${p.pos==='SG'?'selected':''}>SG</option>
                        <option value="SF" ${p.pos==='SF'?'selected':''}>SF</option>
                        <option value="PF" ${p.pos==='PF'?'selected':''}>PF</option>
                        <option value="C" ${p.pos==='C'?'selected':''}>C</option>
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm text-danger" onclick="removePlayer(${index})"><i class="fas fa-times"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function addPlayerRow() {
        currentPlayers.push({name: '', number: '', pos: 'PG'});
        renderRows();
    }

    function removePlayer(index) {
        currentPlayers.splice(index, 1);
        renderRows();
    }

    function updatePlayer(index, field, value) {
        currentPlayers[index][field] = value;
    }

    function saveRosterData() {
        // Filter out empty names
        const cleanPlayers = currentPlayers.filter(p => p.name.trim() !== '');
        document.getElementById('modalPlayersJson').value = JSON.stringify(cleanPlayers);
    }
</script>
@endpush