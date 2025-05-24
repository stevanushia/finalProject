@extends('layouts.app')

@section('title', 'Player Statistics')

@section('content')
<div class="container py-5">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-primary text-white rounded p-4">
                <h1 class="display-4 mb-2">Player Statistics</h1>
                <p class="lead mb-0">
                    Welcome back, {{ $statistics['user']['displayName'] ?? 'Player' }}!
                    @if(isset($statistics['user']['isPremium']) && $statistics['user']['isPremium'])
                        <span class="badge bg-warning text-dark ms-2">PREMIUM</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-5">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-basketball-ball fa-2x mb-3"></i>
                    <h3 class="card-title">{{ $statistics['totalGames'] }}</h3>
                    <p class="card-text">Total Games</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-2x mb-3"></i>
                    <h3 class="card-title">{{ $statistics['totalPoints'] }}</h3>
                    <p class="card-text">Total Points</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 bg-warning text-dark">
                <div class="card-body text-center">
                    <i class="fas fa-bullseye fa-2x mb-3"></i>
                    <h3 class="card-title">{{ $statistics['averagePointsPerGame'] }}</h3>
                    <p class="card-text">Avg Points/Game</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 bg-danger text-white">
                <div class="card-body text-center">
                    <i class="fas fa-target fa-2x mb-3"></i>
                    <h3 class="card-title">{{ $statistics['totalShots'] }}</h3>
                    <p class="card-text">Total Shots</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Shot Breakdown -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Shot Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-4 text-center">
                            <div class="bg-success text-white rounded p-3 mb-2">
                                <h4>{{ $statistics['threePointers'] }}</h4>
                            </div>
                            <small class="text-muted">3-Pointers</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="bg-info text-white rounded p-3 mb-2">
                                <h4>{{ $statistics['twoPointers'] }}</h4>
                            </div>
                            <small class="text-muted">2-Pointers</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="bg-warning text-dark rounded p-3 mb-2">
                                <h4>{{ $statistics['freeThrows'] }}</h4>
                            </div>
                            <small class="text-muted">Free Throws</small>
                        </div>
                    </div>
                    
                    @if($statistics['totalShots'] > 0)
                    <div class="mt-4">
                        <h6>Shooting Accuracy</h6>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ ($statistics['threePointers']/$statistics['totalShots'])*100 }}%">
                                3PT: {{ round(($statistics['threePointers']/$statistics['totalShots'])*100, 1) }}%
                            </div>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-info" role="progressbar" 
                                 style="width: {{ ($statistics['twoPointers']/$statistics['totalShots'])*100 }}%">
                                2PT: {{ round(($statistics['twoPointers']/$statistics['totalShots'])*100, 1) }}%
                            </div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                 style="width: {{ ($statistics['freeThrows']/$statistics['totalShots'])*100 }}%">
                                FT: {{ round(($statistics['freeThrows']/$statistics['totalShots'])*100, 1) }}%
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quarter Performance -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clock me-2"></i>Performance by Quarter
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($statistics['quarterBreakdown'] as $quarter => $points)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">{{ $quarter }}</span>
                        <div class="flex-grow-1 mx-3">
                            <div class="progress">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                     style="width: {{ $statistics['totalPoints'] > 0 ? ($points/$statistics['totalPoints'])*100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-primary">{{ $points }} pts</span>
                    </div>
                    @endforeach
                    
                    <div class="mt-4 p-3 bg-light rounded">
                        <small class="text-muted">
                            <strong>Best Quarter:</strong> 
                            {{ collect($statistics['quarterBreakdown'])->keys()->first() }} 
                            ({{ collect($statistics['quarterBreakdown'])->max() }} points)
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Game History -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Recent Games
                    </h5>
                    <small>{{ count($statistics['gameHistory']) }} games total</small>
                </div>
                <div class="card-body">
                    @if(count($statistics['gameHistory']) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Game</th>
                                        <th>Teams</th>
                                        <th>Score</th>
                                        <th>Quarter</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($statistics['gameHistory'], -10) as $game)
                                    <tr>
                                        <td>
                                            <strong>{{ $game['sessionName'] }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary me-1">{{ $game['homeTeam'] }}</span>
                                            vs
                                            <span class="badge bg-secondary ms-1">{{ $game['awayTeam'] }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold">
                                                {{ $game['homeScore'] }} - {{ $game['awayScore'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $game['quarter'] }}</span>
                                        </td>
                                        <td>
                                            @if($game['isCompleted'])
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                <span class="badge bg-warning text-dark">In Progress</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-basketball-ball fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No games played yet</h4>
                            <p class="text-muted">Start playing to see your statistics here!</p>
                            {{-- <a href="{{ route('game.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Create New Game
                            </a> --}}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('assets/js/core.min.js') }}"></script>
    <script src="{{ asset('assets/js/script.js') }}"></script>
@endpush
@endsection