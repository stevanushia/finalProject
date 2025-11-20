@extends('layouts.app')

@section('title', 'Create Tournament')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0">
                
                {{-- Header Section --}}
                <div class="card-header bg-primary text-white py-3 position-relative" style="z-index: 2;">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-trophy me-2"></i>Create New Tournament</h4>
                </div>

                {{-- Body Section --}}
                <div class="card-body p-4 bg-white position-relative" style="z-index: 1;">
                    <div class="mt-2"></div> 
                    
                    <form action="{{ route('tournaments.store') }}" method="POST" id="tournamentForm">
                        @csrf
                        
                        {{-- 1. Tournament Name (Full Width) --}}
                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold">Tournament Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-heading text-muted"></i></span>
                                <input type="text" class="form-control" id="name" name="name" placeholder="e.g., Winter Cup 2025" required>
                            </div>
                        </div>

                        {{-- 2. Date and Size (Side-by-Side) --}}
                        <div class="row g-3 mb-4">
                            {{-- NEW: Start Date Field with MIN attribute --}}
                            <div class="col-md-6">
                                <label for="start_date" class="form-label fw-bold">Start Date</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-calendar-alt text-muted"></i></span>
                                    <input type="date" 
                                           class="form-control" 
                                           id="start_date" 
                                           name="start_date" 
                                           required 
                                           value="{{ date('Y-m-d') }}"
                                           min="{{ date('Y-m-d') }}"
                                           style="cursor: pointer"> {{-- THIS DISABLES PAST DATES --}}
                                </div>
                            </div>

                            {{-- Team Count Selector --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Number of Teams</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="participant_count" id="size4" value="4" onchange="generateTeamInputs(4)">
                                    <label class="btn btn-outline-primary" for="size4">4</label>

                                    <input type="radio" class="btn-check" name="participant_count" id="size8" value="8" checked onchange="generateTeamInputs(8)">
                                    <label class="btn btn-outline-primary" for="size8">8</label>

                                    <input type="radio" class="btn-check" name="participant_count" id="size16" value="16" onchange="generateTeamInputs(16)">
                                    <label class="btn btn-outline-primary" for="size16">16</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 text-muted">

                        {{-- Teams Section --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label fw-bold mb-0">Registered Teams</label>
                                <small class="text-muted">Enter names for all participants</small>
                            </div>
                            
                            <div id="teamInputs" class="row g-3">
                                {{-- JavaScript will populate this --}}
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('tournaments.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold">Generate Bracket</button>
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

    // Initialize with the checked value (8)
    document.addEventListener('DOMContentLoaded', () => generateTeamInputs(8));
</script>
@endpush
@endsection