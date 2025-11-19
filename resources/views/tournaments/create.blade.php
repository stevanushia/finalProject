@extends('layouts.app')

@section('title', 'Create Tournament')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-trophy me-2"></i>Create New Tournament</h4>
                </div>
                <div class="card-body p-4 bg-light">
                    <form action="{{ route('tournaments.store') }}" method="POST" id="tournamentForm">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold">Tournament Name</label>
                            <input type="text" class="form-control form-control-lg" id="name" name="name" placeholder="e.g., Winter Cup 2025" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Number of Teams</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="participant_count" id="size4" value="4" onchange="generateTeamInputs(4)">
                                <label class="btn btn-outline-primary" for="size4">4 Teams</label>

                                <input type="radio" class="btn-check" name="participant_count" id="size8" value="8" checked onchange="generateTeamInputs(8)">
                                <label class="btn btn-outline-primary" for="size8">8 Teams</label>

                                <input type="radio" class="btn-check" name="participant_count" id="size16" value="16" onchange="generateTeamInputs(16)">
                                <label class="btn btn-outline-primary" for="size16">16 Teams</label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3">Registered Teams</label>
                            <div id="teamInputs" class="row g-3">
                                </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg fw-bold">Generate Bracket</button>
                            <a href="{{ route('tournaments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function generateTeamInputs(count) {
        const container = document.getElementById('teamInputs');
        container.innerHTML = '';
        
        for (let i = 1; i <= count; i++) {
            const div = document.createElement('div');
            div.className = 'col-md-6';
            div.innerHTML = `
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted">#${i}</span>
                    <input type="text" class="form-control" name="teams[]" placeholder="Team Name ${i}" required>
                </div>
            `;
            container.appendChild(div);
        }
    }

    // Initialize with 8
    document.addEventListener('DOMContentLoaded', () => generateTeamInputs(8));
</script>
@endpush
@endsection