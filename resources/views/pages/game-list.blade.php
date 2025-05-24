@extends('layouts.app')

@section('title', 'Game Sessions List')

@section('content')
{{-- <div class="container py-5">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-primary rounded p-4 shadow">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-5 mb-2 text-white">Game Sessions</h1>
                        <p class="lead mb-0 text-white">Browse all basketball game sessions and their status</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="btn-group" role="group" aria-label="Filter games">
                            <button type="button" class="btn btn-light fw-bold active" data-filter="all">All</button>
                            <button type="button" class="btn btn-light fw-bold" data-filter="in-progress">In Progress</button>
                            <button type="button" class="btn btn-light fw-bold" data-filter="completed">Completed</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Game List -->
    <div class="row" id="game-list">
        @forelse($games as $gameId => $game)
            <div class="col-lg-4 col-md-6 mb-4 game-card" data-status="{{ $game['game_state']['isMatchEnded'] ? 'completed' : 'in-progress' }}">
                <div class="card h-100 shadow {{ $game['game_state']['isMatchEnded'] ? 'border-success' : 'border-warning' }}">
                    <div class="card-header {{ $game['game_state']['isMatchEnded'] ? 'bg-success' : 'bg-warning' }} text-dark">
                        <h5 class="mb-0 fw-bold">{{ $game['game_state']['sessionName'] ?? 'Unknown Game' }}</h5>
                    </div>
                    <div class="card-body bg-light text-center">
                        <div class="fs-2 fw-bold mb-2">
                            {{ $game['game_state']['homeScore'] ?? 0 }} - {{ $game['game_state']['awayScore'] ?? 0 }}
                        </div>
                        <p class="mb-1 text-dark fw-semibold">{{ $game['game_state']['homeTeamName'] ?? 'HOME' }} vs {{ $game['game_state']['awayTeamName'] ?? 'AWAY' }}</p>
                        <small class="text-muted">{{ $game['game_state']['quarter'] ?? 'Q1' }}</small>
                        <div class="mt-3">
                            <span class="badge {{ $game['game_state']['isMatchEnded'] ? 'badge-primary' : 'badge-secondary' }} text-dark fw-bold">
                                {{ $game['game_state']['isMatchEnded'] ? 'COMPLETED' : 'IN PROGRESS' }}
                            </span>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <a href="{{ route('game.overview.specific', ['gameId' => $gameId]) }}" class="btn btn-primary btn-sm fw-bold">View Details</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-4">
                <i class="fas fa-basketball-ball fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No game sessions available</h5>
            </div>
        @endforelse
    </div>
</div> --}}

<div class="container py-5">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-primary rounded p-4 shadow">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-5 mb-2 text-white">Game Sessions</h1>
                        <p class="lead mb-0 text-white">Browse all basketball game sessions and their status</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="btn-group" role="group" aria-label="Filter games">
                            <button type="button" class="btn btn-light fw-bold active" data-filter="all">All</button>
                            <button type="button" class="btn btn-light fw-bold" data-filter="in-progress">In Progress</button>
                            <button type="button" class="btn btn-light fw-bold" data-filter="completed">Completed</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Game List -->
    <div class="row" id="game-list">
        @forelse($games as $gameId => $game)
            <div class="col-lg-4 col-md-6 mb-4 game-card" data-status="{{ $game['game_state']['isMatchEnded'] ? 'completed' : 'in-progress' }}">
                <div class="card h-100 shadow {{ $game['game_state']['isMatchEnded'] ? 'border-success' : 'border-warning' }}">
                    <div class="card-header {{ $game['game_state']['isMatchEnded'] ? 'bg-success' : 'bg-warning' }} text-dark">
                        <h5 class="mb-0 fw-bold text-white">{{ $game['game_state']['sessionName'] ?? 'Unknown Game' }}</h5>
                    </div>
                    <div class="card-body bg-light text-center">
                        <div class="fs-2 fw-bold mb-2">
                            {{ $game['game_state']['homeScore'] ?? 0 }} - {{ $game['game_state']['awayScore'] ?? 0 }}
                        </div>
                        <p class="mb-1 text-dark fw-semibold">{{ $game['game_state']['homeTeamName'] ?? 'HOME' }} vs {{ $game['game_state']['awayTeamName'] ?? 'AWAY' }}</p>
                        <small class="text-muted">{{ $game['game_state']['quarter'] ?? 'Q1' }}</small>
                        <div class="mt-3">
                            <span class="badge {{ $game['game_state']['isMatchEnded'] ? 'bg-success' : 'bg-warning' }} text-white fw-bold">
                                {{ $game['game_state']['isMatchEnded'] ? 'COMPLETED' : 'IN PROGRESS' }}
                            </span>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <a href="{{ route('game.overview.specific', ['gameId' => $gameId]) }}" class="btn btn-primary btn-sm fw-bold">View Details</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-4">
                <i class="fas fa-basketball-ball fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No game sessions available</h5>
            </div>
        @endforelse
    </div>
</div>

@push('styles')
<style>
    .game-card {
        transition: transform 0.2s ease-in-out;
    }

    .game-card:hover {
        transform: translateY(-2px);
    }

    .btn-group .btn {
        border-radius: 0;
        margin: 0 2px;
        transition: all 0.2s;
    }

    .btn-group .btn.active {
        background-color: #28a745 !important;
        color: white !important;
    }

    .btn-group .btn:hover {
        background-color: #e9ecef;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/core.min.js') }}"></script>
<script src="{{ asset('assets/js/script.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterButtons = document.querySelectorAll('.btn-group .btn');
        const gameCards = document.querySelectorAll('.game-card');

        filterButtons.forEach(button => {
            button.addEventListener('click', function () {
                // Toggle active class
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                // Filter games
                const filter = this.getAttribute('data-filter');
                gameCards.forEach(card => {
                    const status = card.getAttribute('data-status');
                    if (filter === 'all' || status === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    });
</script>
@endpush
@endsection
