@extends('layouts.app')

@section('title', 'Game Overview - ' . $overview['sessionName'])

@section('content')
<div class="container py-5">
    <!-- Game Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-success rounded p-4 shadow">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-5 mb-2 fw-bold text-white">{{ $overview['sessionName'] }}</h1>
                        <p class="lead mb-0 text-white">
                            Game Overview & Team Performance Analysis
                        </p>
                        @if($overview['isCompleted'])
                            <span class="badge bg-light text-success ms-2 fw-bold">COMPLETED</span>
                        @else
                            <span class="badge bg-warning text-dark ms-2 fw-bold">{{ $overview['quarter'] }} - IN PROGRESS</span>
                        @endif
                    </div>
                    <div class="col-md-4 text-end" style="font-weight: bold; text-align: center">
                        <div class="fs-1 fw-bold text-white">
                            {{ $overview['homeScore'] }} - {{ $overview['awayScore'] }}
                        </div>  
                        <small class="text-light">{{ $overview['homeTeam'] }} vs {{ $overview['awayTeam'] }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $isHomeWinner = $overview['homeScore'] > $overview['awayScore'];
        $isAwayWinner = $overview['awayScore'] > $overview['homeScore'];
        $isTie = $overview['homeScore'] == $overview['awayScore'];
    @endphp

    <!-- Team Comparison Cards -->
    <div class="row mb-5">
        <div class="col-lg-6 mb-3">
            @php
                $homeCardClass = $isHomeWinner ? 'border-success' : ($isTie ? 'border-warning' : 'border-danger');
                $homeHeaderClass = $isHomeWinner ? 'bg-success' : ($isTie ? 'bg-warning' : 'bg-danger');
                $homeTextClass = $isHomeWinner ? 'text-success' : ($isTie ? 'text-warning' : 'text-danger');
            @endphp
            <div class="card h-100 {{ $homeCardClass }} shadow">
                <div class="card-header {{ $homeHeaderClass }} text-center">
                    <h4 class="mb-0 fw-bold text-white">{{ $overview['homeTeam'] }} (HOME)</h4>
                </div>
                <div class="card-body text-center bg-light">
                    <div class="display-3 fw-bold {{ $homeTextClass }} mb-3">{{ $overview['homeScore'] }}</div>
                    <div class="row">
                        <div class="col-6">
                            <h6 class="text-dark fw-bold">Shooting Efficiency</h6>
                            <div class="fs-4 fw-bold text-dark">{{ $overview['teamComparison']['home']['efficiency'] }}%</div>
                        </div>
                        <div class="col-6">
                            <h6 class="text-dark fw-bold">Total Shots</h6>
                            <div class="fs-4 fw-bold text-dark">{{ $overview['teamComparison']['home']['totalShots'] }}</div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <small class="text-dark fw-semibold">Fouls: {{ $overview['homeFouls'] }}</small>
                        </div>
                        <div class="col-6">
                            <small class="text-dark fw-semibold">Timeouts: {{ $overview['homeTimeouts'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            @php
                $awayCardClass = $isAwayWinner ? 'border-success' : ($isTie ? 'border-warning' : 'border-danger');
                $awayHeaderClass = $isAwayWinner ? 'bg-success' : ($isTie ? 'bg-warning' : 'bg-danger');
                $awayTextClass = $isAwayWinner ? 'text-success' : ($isTie ? 'text-warning' : 'text-danger');
            @endphp
            <div class="card h-100 {{ $awayCardClass }} shadow">
                <div class="card-header {{ $awayHeaderClass }} text-white text-center">
                    <h4 class="mb-0 fw-bold text-white">{{ $overview['awayTeam'] }} (AWAY)</h4>
                </div>
                <div class="card-body text-center bg-light">
                    <div class="display-3 fw-bold {{ $awayTextClass }} mb-3">{{ $overview['awayScore'] }}</div>
                    <div class="row">
                        <div class="col-6">
                            <h6 class="text-dark fw-bold">Shooting Efficiency</h6>
                            <div class="fs-4 fw-bold text-dark">{{ $overview['teamComparison']['away']['efficiency'] }}%</div>
                        </div>
                        <div class="col-6">
                            <h6 class="text-dark fw-bold">Total Shots</h6>
                            <div class="fs-4 fw-bold text-dark">{{ $overview['teamComparison']['away']['totalShots'] }}</div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <small class="text-dark fw-semibold">Fouls: {{ $overview['awayFouls'] }}</small>
                        </div>
                        <div class="col-6">
                            <small class="text-dark fw-semibold">Timeouts: {{ $overview['awayTimeouts'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quarter Performance Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100 shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-chart-bar me-2"></i>Quarter Performance
                    </h5>
                </div>
                <div class="card-body bg-light">
                    <div style="height: 250px; position: relative; width: 100%; overflow: hidden;">
                        <canvas id="quarterChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="row text-center">
                            @foreach($overview['quarterPerformance']['home'] as $quarter => $homePoints)
                            <div class="col-3">
                                <div class="border rounded p-2 bg-white shadow-sm">
                                    <small class="text-dark fw-bold d-block">{{ $quarter }}</small>
                                    <div class="fw-bold {{ $isHomeWinner ? 'text-success' : ($isTie ? 'text-warning' : 'text-danger') }}">{{ $homePoints }}</div>
                                    <div class="fw-bold {{ $isAwayWinner ? 'text-success' : ($isTie ? 'text-warning' : 'text-danger') }}">{{ $overview['quarterPerformance']['away'][$quarter] ?? 0 }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shot Breakdown -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100 shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-basketball-ball me-2"></i>Shot Analysis
                    </h5>
                </div>
                <div class="card-body bg-light">
                    <div style="height: 250px; position: relative; width: 100%; overflow: hidden;">
                        <canvas id="shotChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-6" style="text-align: center">
                                <h6 class="fw-bold {{ $isHomeWinner ? 'text-success' : ($isTie ? 'text-warning' : 'text-danger') }}">{{ $overview['homeTeam'] }}</h6>
                                <small class="d-block text-dark fw-semibold">3PT: {{ $overview['scoringBreakdown']['home']['3PT'] }}</small>
                                <small class="d-block text-dark fw-semibold">2PT: {{ $overview['scoringBreakdown']['home']['2PT'] }}</small>
                                <small class="d-block text-dark fw-semibold">FT: {{ $overview['scoringBreakdown']['home']['1PT'] }}</small>
                            </div>
                            <div class="col-6 text-center">
                                <h6 class="fw-bold {{ $isAwayWinner ? 'text-success' : ($isTie ? 'text-warning' : 'text-danger') }}">{{ $overview['awayTeam'] }}</h6>
                                <small class="d-block text-dark fw-semibold">3PT: {{ $overview['scoringBreakdown']['away']['3PT'] }}</small>
                                <small class="d-block text-dark fw-semibold">2PT: {{ $overview['scoringBreakdown']['away']['2PT'] }}</small>
                                <small class="d-block text-dark fw-semibold">FT: {{ $overview['scoringBreakdown']['away']['1PT'] }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Player Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-dark">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-users me-2"></i>&nbsp;&nbsp; Top Performers
                    </h5>
                </div>
                <div class="card-body bg-light">
                    @if(count($overview['playerStats']) > 0)
                        <div class="row">
                            @foreach(array_slice($overview['playerStats'], 0, 6) as $index => $player)
                            @php
                                $isPlayerOnWinningTeam = ($player['team'] === $overview['homeTeam'] && $isHomeWinner) || 
                                                        ($player['team'] === $overview['awayTeam'] && $isAwayWinner);
                                $playerBadgeClass = $isPlayerOnWinningTeam ? 'bg-success' : ($isTie ? 'bg-warning' : 'bg-danger');
                                $playerBorderClass = $isPlayerOnWinningTeam ? 'border-success' : ($isTie ? 'border-warning' : 'border-danger');
                            @endphp
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card {{ $playerBorderClass }} shadow-sm">
                                    <div class="card-body text-center bg-white">
                                        <h6 class="card-title text-dark fw-bold">{{ $player['name'] }}</h6>
                                        <div class="badge {{ $playerBadgeClass }} mb-2 fw-bold">
                                            {{ $player['team'] }}
                                        </div>
                                        <div class="display-6 fw-bold text-dark">{{ $player['points'] }}</div>
                                        <small class="text-dark fw-semibold">Points</small>
                                        <div class="mt-2">
                                            <small class="d-block text-dark fw-semibold">3PT: {{ $player['shots']['3PT'] }}</small>
                                            <small class="d-block text-dark fw-semibold">2PT: {{ $player['shots']['2PT'] }}</small>
                                            <small class="d-block text-dark fw-semibold">FT: {{ $player['shots']['1PT'] }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No player statistics available</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Game Timeline -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-clock me-2"></i>Game Timeline
                    </h5>
                </div>
                <div class="card-body bg-light">
                    @if(count($overview['gameTimeline']) > 0)
                        <div class="timeline-container" style="max-height: 400px; overflow-y: auto;">
                            @foreach(array_reverse($overview['gameTimeline']) as $event)
                            @php
                                $isEventTeamWinning = ($event['team'] === $overview['homeTeam'] && $isHomeWinner) || 
                                                    ($event['team'] === $overview['awayTeam'] && $isAwayWinner);
                                $eventBgClass = $isEventTeamWinning ? 'bg-success bg-opacity-10 border-start border-success border-3' : 
                                            ($isTie ? 'bg-warning bg-opacity-10 border-start border-warning border-3' : 
                                                'bg-danger bg-opacity-10 border-start border-danger border-3');
                                $eventBadgeClass = $isEventTeamWinning ? 'bg-success' : ($isTie ? 'bg-warning' : 'bg-danger');
                            @endphp
                            <div class="d-flex mb-3 p-3 {{ $eventBgClass }} rounded shadow-sm">
                                <div class="flex-shrink-0">
                                    <span class="badge {{ $eventBadgeClass }} fw-bold">
                                        {{ $event['quarter'] }}
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-bold text-white">{{ $event['player'] }} ({{ $event['team'] }})</div>
                                    <small class="text-white fw-semibold">{{ $event['shotType'] }} - {{ $event['points'] }} points</small>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="fw-bold text-white">{{ $event['homeScore'] }} - {{ $event['awayScore'] }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No game events recorded</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .timeline-container {
        scrollbar-width: thin;
        scrollbar-color: #28a745 transparent;
    }
    
    .timeline-container::-webkit-scrollbar {
        width: 6px;
    }
    
    .timeline-container::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .timeline-container::-webkit-scrollbar-thumb {
        background-color: #28a745;
        border-radius: 3px;
    }

    .card {
        transition: transform 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script src="{{ asset('assets/js/core.min.js') }}"></script>
<script src="{{ asset('assets/js/script.js') }}"></script>
<script>
    // Determine winner for chart colors
    const homeScore = {{ $overview['homeScore'] }};
    const awayScore = {{ $overview['awayScore'] }};
    const isHomeWinner = homeScore > awayScore;
    const isAwayWinner = awayScore > homeScore;
    const isTie = homeScore === awayScore;
    
    // Dynamic color assignment
    const homeColor = isHomeWinner ? 'rgba(40, 167, 69, 0.8)' : (isTie ? 'rgba(255, 193, 7, 0.8)' : 'rgba(220, 53, 69, 0.8)');
    const homeBorderColor = isHomeWinner ? 'rgba(40, 167, 69, 1)' : (isTie ? 'rgba(255, 193, 7, 1)' : 'rgba(220, 53, 69, 1)');
    const awayColor = isAwayWinner ? 'rgba(40, 167, 69, 0.8)' : (isTie ? 'rgba(255, 193, 7, 0.8)' : 'rgba(220, 53, 69, 0.8)');
    const awayBorderColor = isAwayWinner ? 'rgba(40, 167, 69, 1)' : (isTie ? 'rgba(255, 193, 7, 1)' : 'rgba(220, 53, 69, 1)');

    // Quarter Performance Chart
    const quarterCtx = document.getElementById('quarterChart').getContext('2d');
    const quarterChart = new Chart(quarterCtx, {
        type: 'bar',
        data: {
            labels: ['Q1', 'Q2', 'Q3', 'Q4'],
            datasets: [{
                label: '{{ $overview['homeTeam'] }}',
                data: [
                    {{ $overview['quarterPerformance']['home']['Q1'] }},
                    {{ $overview['quarterPerformance']['home']['Q2'] }},
                    {{ $overview['quarterPerformance']['home']['Q3'] }},
                    {{ $overview['quarterPerformance']['home']['Q4'] }}
                ],
                backgroundColor: homeColor,
                borderColor: homeBorderColor,
                borderWidth: 1
            }, {
                label: '{{ $overview['awayTeam'] }}',
                data: [
                    {{ $overview['quarterPerformance']['away']['Q1'] }},
                    {{ $overview['quarterPerformance']['away']['Q2'] }},
                    {{ $overview['quarterPerformance']['away']['Q3'] }},
                    {{ $overview['quarterPerformance']['away']['Q4'] }}
                ],
                backgroundColor: awayColor,
                borderColor: awayBorderColor,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 10,
                    bottom: 10,
                    left: 10,
                    right: 10
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        color: '#333'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        color: '#333'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: '#333',
                        font: {
                            weight: 'bold'
                        }
                    }
                },
                title: {
                    display: true,
                    text: 'Points by Quarter',
                    color: '#333',
                    font: {
                        size: 14,
                        weight: 'bold'
                    }
                }
            }
        }
    });

    // Shot Analysis Chart - Dynamic colors based on winning status
    const homeColors = {
        '3PT': isHomeWinner ? 'rgba(40, 167, 69, 0.9)' : (isTie ? 'rgba(255, 193, 7, 0.9)' : 'rgba(220, 53, 69, 0.9)'),
        '2PT': isHomeWinner ? 'rgba(40, 167, 69, 0.7)' : (isTie ? 'rgba(255, 193, 7, 0.7)' : 'rgba(220, 53, 69, 0.7)'),
        '1PT': isHomeWinner ? 'rgba(40, 167, 69, 0.5)' : (isTie ? 'rgba(255, 193, 7, 0.5)' : 'rgba(220, 53, 69, 0.5)')
    };
    
    const awayColors = {
        '3PT': isAwayWinner ? 'rgba(40, 167, 69, 0.9)' : (isTie ? 'rgba(255, 193, 7, 0.9)' : 'rgba(220, 53, 69, 0.9)'),
        '2PT': isAwayWinner ? 'rgba(40, 167, 69, 0.7)' : (isTie ? 'rgba(255, 193, 7, 0.7)' : 'rgba(220, 53, 69, 0.7)'),
        '1PT': isAwayWinner ? 'rgba(40, 167, 69, 0.5)' : (isTie ? 'rgba(255, 193, 7, 0.5)' : 'rgba(220, 53, 69, 0.5)')
    };

    const homeBorderColors = {
        border: isHomeWinner ? 'rgba(40, 167, 69, 1)' : (isTie ? 'rgba(255, 193, 7, 1)' : 'rgba(220, 53, 69, 1)')
    };
    
    const awayBorderColors = {
        border: isAwayWinner ? 'rgba(40, 167, 69, 1)' : (isTie ? 'rgba(255, 193, 7, 1)' : 'rgba(220, 53, 69, 1)')
    };

    const shotCtx = document.getElementById('shotChart').getContext('2d');
    const shotChart = new Chart(shotCtx, {
        type: 'doughnut',
        data: {
            labels: [
                '{{ $overview['homeTeam'] }} 3PT',
                '{{ $overview['homeTeam'] }} 2PT', 
                '{{ $overview['homeTeam'] }} 1PT',
                '{{ $overview['awayTeam'] }} 3PT',
                '{{ $overview['awayTeam'] }} 2PT',
                '{{ $overview['awayTeam'] }} 1PT'
            ],
            datasets: [{
                data: [
                    {{ $overview['scoringBreakdown']['home']['3PT'] }},
                    {{ $overview['scoringBreakdown']['home']['2PT'] }},
                    {{ $overview['scoringBreakdown']['home']['1PT'] }},
                    {{ $overview['scoringBreakdown']['away']['3PT'] }},
                    {{ $overview['scoringBreakdown']['away']['2PT'] }},
                    {{ $overview['scoringBreakdown']['away']['1PT'] }}
                ],
                backgroundColor: [
                    homeColors['3PT'],
                    homeColors['2PT'],
                    homeColors['1PT'],
                    awayColors['3PT'],
                    awayColors['2PT'],
                    awayColors['1PT']
                ],
                borderColor: [
                    homeBorderColors.border,
                    homeBorderColors.border,
                    homeBorderColors.border,
                    awayBorderColors.border,
                    awayBorderColors.border,
                    awayBorderColors.border
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 10,
                    bottom: 10,
                    left: 10,
                    right: 10
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 10,
                            weight: 'bold'
                        },
                        padding: 8,
                        color: '#333'
                    }
                },
                title: {
                    display: true,
                    text: 'Shot Distribution',
                    color: '#333',
                    font: {
                        size: 14,
                        weight: 'bold'
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection