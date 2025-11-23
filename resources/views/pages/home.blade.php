@extends('layouts.app')

@section('title', 'CourtSide - Basketball Analytics')

@section('content')

<div class="position-relative bg-dark text-white overflow-hidden" style="min-height: 500px;">
    <div class="position-absolute top-0 start-0 w-100 h-100" 
         style="background: url('{{ asset('assets/images/home-hero.jpg') }}') center/cover no-repeat;">
        <div class="w-100 h-100" style="background: linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.6) 100%);"></div>
    </div>

    <div class="container position-relative h-100 d-flex align-items-center" style="min-height: 500px;">
        <div class="row">
            <div class="col-lg-7">
                <span class="badge bg-warning text-dark fw-bold mb-3 px-3 py-2 text-uppercase ls-1">Stats Tracker</span>
                <h1 class="display-3 fw-bold mb-4" style="color: white">Elevate Your <br><span class="text-primary">Game IQ</span></h1>
                <p class="lead text-white-50 mb-5 w-75">
                    Track every point, assist, and rebound in real-time. Manage tournaments and analyze performance with professional-grade tools.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('game.list') }}" class="btn btn-primary btn-lg px-5 fw-bold shadow-lg">
                        <i class="fas fa-play me-2"></i> Start Game
                    </a>
                    <a href="{{ route('tournaments.index') }}" class="btn btn-outline-light btn-lg px-5 fw-bold">
                        <i class="fas fa-trophy me-2"></i> Tournaments
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section section-md bg-light">
    <div class="container">
        
        <div class="row g-4 mb-5 mt-n6 position-relative" style="z-index: 10;">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 bg-white">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-4 text-primary">
                            <i class="fas fa-basketball-ball fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Live Tracking</h5>
                            <p class="text-muted small mb-0">Real-time score & stats entry</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 bg-white">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded-3 me-4 text-success">
                            <i class="fas fa-chart-pie fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Deep Analytics</h5>
                            <p class="text-muted small mb-0">Shot charts & efficiency ratings</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 bg-white">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 p-3 rounded-3 me-4 text-info">
                            <i class="fas fa-sitemap fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Tournament Mode</h5>
                            <p class="text-muted small mb-0">Automated brackets & standings</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-5">
            <div class="col-lg-8">
                
                @if(count($liveGames) > 0)
                <div class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-circle text-danger me-2 fa-xs"></i>Live Now
                        </h4>
                    </div>
                    
                    @foreach($liveGames as $game)
                    <div class="card border-0 shadow-sm mb-3 overflow-hidden match-card">
                        <div class="card-body p-0">
                            <div class="row g-0">
                                <div class="col-md-9 p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="badge bg-danger bg-opacity-10 text-danger fw-bold px-3 py-2">
                                            <i class="fas fa-clock me-1"></i> {{ $game['quarter'] }}
                                        </span>
                                        <small class="text-muted fw-bold text-uppercase tracking-wider">{{ $game['name'] }}</small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-center" style="width: 40%;">
                                            <h5 class="fw-bold mb-0 text-truncate">{{ $game['home'] }}</h5>
                                        </div>
                                        <div class="text-center bg-light rounded-pill px-4 py-2 fw-bold fs-4" style="min-width: 120px;">
                                            {{ $game['homeScore'] }} : {{ $game['awayScore'] }}
                                        </div>
                                        <div class="text-center" style="width: 40%;">
                                            <h5 class="fw-bold mb-0 text-truncate">{{ $game['away'] }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 bg-primary text-white d-flex flex-column align-items-center justify-content-center p-3 text-center">
                                    <a href="{{ route('game.overview.specific', $game['id']) }}" class="btn btn-light fw-bold btn-sm w-100 stretched-link">
                                        Watch Live
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0 text-dark">Recent Results</h4>
                        <a href="{{ route('game.list') }}" class="btn btn-link text-decoration-none fw-bold">View All</a>
                    </div>

                    @forelse($recentGames as $game)
                    <div class="card border-0 shadow-sm mb-3 match-card hover-lift">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <div class="col-3 text-end">
                                    <span class="fw-bold {{ $game['homeScore'] > $game['awayScore'] ? 'text-dark' : 'text-muted' }}">{{ $game['home'] }}</span>
                                </div>
                                <div class="col-2 text-center">
                                    <span class="badge bg-light text-dark border px-3 py-2 fw-bold">
                                        {{ $game['homeScore'] }} - {{ $game['awayScore'] }}
                                    </span>
                                </div>
                                <div class="col-3 text-start">
                                    <span class="fw-bold {{ $game['awayScore'] > $game['homeScore'] ? 'text-dark' : 'text-muted' }}">{{ $game['away'] }}</span>
                                </div>
                                <div class="col-4 text-end">
                                    <span class="text-muted small me-3 d-none d-md-inline">
                                        {{ $game['timestamp'] ? \Carbon\Carbon::createFromTimestampMs($game['timestamp'])->diffForHumans() : 'Finished' }}
                                    </span>
                                    <a href="{{ route('game.overview.specific', $game['id']) }}" class="btn btn-sm btn-outline-secondary rounded-circle">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 border rounded-3 bg-white border-dashed">
                        <i class="fas fa-basketball-ball fa-3x text-muted mb-3 opacity-50"></i>
                        <h5 class="text-muted">No games played yet.</h5>
                        <a href="{{ route('game.list') }}" class="btn btn-sm btn-primary mt-2">Record First Game</a>
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="col-lg-4">
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold m-0">Upcoming Tournaments</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse($upcomingTournaments as $t)
                        <a href="{{ route('tournaments.show', $t['id']) }}" class="list-group-item list-group-item-action p-3 border-0 border-bottom">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 fw-bold text-primary">{{ $t['name'] }}</h6>
                                <small class="badge bg-light text-dark">{{ $t['teams'] }} Teams</small>
                            </div>
                            <small class="text-muted">
                                <i class="far fa-calendar me-1"></i> {{ \Carbon\Carbon::createFromTimestampMs($t['startDate'])->format('M d, Y') }}
                            </small>
                        </a>
                        @empty
                        <div class="p-4 text-center">
                            <p class="text-muted small mb-0">No upcoming tournaments.</p>
                        </div>
                        @endforelse
                        <div class="card-footer bg-white text-center border-0 p-3">
                            <a href="{{ route('tournaments.create') }}" class="btn btn-sm btn-outline-primary w-100">Create Tournament</a>
                        </div>
                    </div>
                </div>

                @if(!Auth::check() || (Auth::check() && !Auth::user()->isPremium))
                <div class="card border-0 shadow-sm bg-gradient text-white overflow-hidden position-relative" 
                     style="background: linear-gradient(135deg, #212529 0%, #495057 100%);">
                    <div class="position-absolute top-0 end-0 p-3 opacity-10">
                        <i class="fas fa-crown fa-6x"></i>
                    </div>
                    
                    <div class="card-body p-4 position-relative">
                        <span class="badge bg-warning text-dark mb-3">PREMIUM</span>
                        <h3 class="fw-bold">Unlock Pro Features</h3>
                        <ul class="list-unstyled mt-3 mb-4 text-white-50">
                            <li class="mb-2" style="color: black"><i class="fas fa-check text-warning me-2"></i> Advanced Shot Charts</li>
                            <li class="mb-2" style="color: black"><i class="fas fa-check text-warning me-2"></i> Unlimited Game History</li>
                            <li class="mb-2" style="color: black"><i class="fas fa-check text-warning me-2"></i> Export Data to PDF</li>
                        </ul>
                        <a href="{{ route('subscription.show') }}" class="btn btn-warning w-100 fw-bold text-dark">
                            Upgrade for Rp 49k
                        </a>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .mt-n6 {
        margin-top: -4rem !important;
    }
    .ls-1 {
        letter-spacing: 1px;
    }
</style>
@endpush