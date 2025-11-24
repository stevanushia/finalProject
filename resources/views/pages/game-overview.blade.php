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
                        <div class="d-flex align-items-center gap-3 mb-2">
                            {{-- Status Badge --}}
                            @if($overview['isCompleted'])
                                <span class="badge bg-white text-success fw-bold px-3 py-2">COMPLETED</span>
                            @else
                                <span class="badge bg-warning text-dark fw-bold px-3 py-2">
                                    <i class="fas fa-circle text-danger fa-xs me-1"></i> LIVE - {{ $overview['quarter'] }}
                                </span>
                            @endif

                            {{-- NEW PRINT BUTTON --}}
                            <a href="{{ route('game.export.pdf', $overview['gameId']) }}" 
                               class="btn btn-sm btn-light text-success fw-bold shadow-sm d-flex align-items-center px-3"
                               target="_blank" 
                               title="Print Game Report">
                                <i class="fas fa-print me-2"></i> PRINT
                            </a>
                        </div>

                        <h1 class="display-5 mb-1 fw-bold text-white">{{ $overview['sessionName'] }}</h1>
                        <p class="lead mb-0 text-white opacity-75" style="font-size: 1rem;">
                            <i class="fas fa-chart-line me-2"></i>Game Overview & Performance Analysis
                        </p>
                    </div>
                    
                    <div class="col-md-4 text-end text-white">
                        <div class="display-4 fw-bold">
                            {{ $overview['homeScore'] }} - {{ $overview['awayScore'] }}
                        </div>  
                        <div class="fs-5 opacity-75">{{ $overview['homeTeam'] }} vs {{ $overview['awayTeam'] }}</div>
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

        <!-- Shot Analysis -->
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
                    <div class="mt-4">
                        <!-- Shot Statistics Grid -->
                        <div class="row g-3">
                            <!-- Home Team Stats -->
                            <div class="col-6">
                                <div class="card border-0 bg-white shadow-sm">
                                    <div class="card-header text-center py-2 {{ $isHomeWinner ? 'bg-success' : ($isTie ? 'bg-warning' : 'bg-danger') }}">
                                        <h6 class="mb-0 fw-bold text-white">{{ $overview['homeTeam'] }}</h6>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="row text-center g-1">
                                            <!-- 3PT Stats -->
                                            <div class="col-4">
                                                <div class="bg-light rounded p-2">
                                                    <div class="fw-bold text-primary mb-1">3PT</div>
                                                    <div class="small">
                                                        <div class="fw-bold text-success">{{ $overview['shotAnalysis']['home']['made']['3PT'] }}</div>
                                                        <div class="text-muted">{{ $overview['shotAnalysis']['home']['total']['3PT'] }}</div>
                                                        <div class="small text-dark">{{ $overview['shotAnalysis']['home']['percentage']['3PT'] }}%</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- 2PT Stats -->
                                            <div class="col-4">
                                                <div class="bg-light rounded p-2">
                                                    <div class="fw-bold text-primary mb-1">2PT</div>
                                                    <div class="small">
                                                        <div class="fw-bold text-success">{{ $overview['shotAnalysis']['home']['made']['2PT'] }}</div>
                                                        <div class="text-muted">{{ $overview['shotAnalysis']['home']['total']['2PT'] }}</div>
                                                        <div class="small text-dark">{{ $overview['shotAnalysis']['home']['percentage']['2PT'] }}%</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- FT Stats -->
                                            <div class="col-4">
                                                <div class="bg-light rounded p-2">
                                                    <div class="fw-bold text-primary mb-1">FT</div>
                                                    <div class="small">
                                                        <div class="fw-bold text-success">{{ $overview['shotAnalysis']['home']['made']['1PT'] }}</div>
                                                        <div class="text-muted">{{ $overview['shotAnalysis']['home']['total']['1PT'] }}</div>
                                                        <div class="small text-dark">{{ $overview['shotAnalysis']['home']['percentage']['1PT'] }}%</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Overall Stats -->
                                        <div class="text-center mt-2 p-2 bg-light rounded">
                                            <div class="small fw-bold text-dark">Overall: {{ $overview['shotAnalysis']['home']['percentage']['overall'] }}%</div>
                                            <div class="small text-muted">{{ $overview['shotAnalysis']['home']['made']['total'] }}/{{ $overview['shotAnalysis']['home']['total']['total'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Away Team Stats -->
                            <div class="col-6">
                                <div class="card border-0 bg-white shadow-sm">
                                    <div class="card-header text-center py-2 {{ $isAwayWinner ? 'bg-success' : ($isTie ? 'bg-warning' : 'bg-danger') }}">
                                        <h6 class="mb-0 fw-bold text-white">{{ $overview['awayTeam'] }}</h6>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="row text-center g-1">
                                            <!-- 3PT Stats -->
                                            <div class="col-4">
                                                <div class="bg-light rounded p-2">
                                                    <div class="fw-bold text-primary mb-1">3PT</div>
                                                    <div class="small">
                                                        <div class="fw-bold text-success">{{ $overview['shotAnalysis']['away']['made']['3PT'] }}</div>
                                                        <div class="text-muted">{{ $overview['shotAnalysis']['away']['total']['3PT'] }}</div>
                                                        <div class="small text-dark">{{ $overview['shotAnalysis']['away']['percentage']['3PT'] }}%</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- 2PT Stats -->
                                            <div class="col-4">
                                                <div class="bg-light rounded p-2">
                                                    <div class="fw-bold text-primary mb-1">2PT</div>
                                                    <div class="small">
                                                        <div class="fw-bold text-success">{{ $overview['shotAnalysis']['away']['made']['2PT'] }}</div>
                                                        <div class="text-muted">{{ $overview['shotAnalysis']['away']['total']['2PT'] }}</div>
                                                        <div class="small text-dark">{{ $overview['shotAnalysis']['away']['percentage']['2PT'] }}%</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- FT Stats -->
                                            <div class="col-4">
                                                <div class="bg-light rounded p-2">
                                                    <div class="fw-bold text-primary mb-1">FT</div>
                                                    <div class="small">
                                                        <div class="fw-bold text-success">{{ $overview['shotAnalysis']['away']['made']['1PT'] }}</div>
                                                        <div class="text-muted">{{ $overview['shotAnalysis']['away']['total']['1PT'] }}</div>
                                                        <div class="small text-dark">{{ $overview['shotAnalysis']['away']['percentage']['1PT'] }}%</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Overall Stats -->
                                        <div class="text-center mt-2 p-2 bg-light rounded">
                                            <div class="small fw-bold text-dark">Overall: {{ $overview['shotAnalysis']['away']['percentage']['overall'] }}%</div>
                                            <div class="small text-muted">{{ $overview['shotAnalysis']['away']['made']['total'] }}/{{ $overview['shotAnalysis']['away']['total']['total'] }}</div>
                                        </div>
                                    </div>
                                </div>
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
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="position-absolute top-0 end-0 opacity-10">
                        <i class="fas fa-basketball-ball" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="card-title mb-0 fw-bold position-relative">
                        <i class="fas fa-users me-3"></i>Player Statistics
                    </h4>
                    <p class="mb-0 mt-1 opacity-75">Individual performance breakdown</p>
                </div>
                <div class="card-body p-0 bg-light">
                    @if(count($overview['playerStats']) > 0)
                        @php
                            // Separate players by team (HOME/AWAY instead of team names)
                            $homePlayers = collect($overview['playerStats'])->where('team', 'HOME')->values();
                            $awayPlayers = collect($overview['playerStats'])->where('team', 'AWAY')->values();
                            
                            // Team tab styling based on winner
                            $homeTabClass = $isHomeWinner ? 'btn-success shadow-sm' : ($isTie ? 'btn-warning shadow-sm' : 'btn-outline-danger');
                            $awayTabClass = $isAwayWinner ? 'btn-success shadow-sm' : ($isTie ? 'btn-warning shadow-sm' : 'btn-outline-danger');
                        @endphp
                        
                        <ul class="nav nav-tabs nav-fill border-0 bg-white shadow-sm" id="playerTabs" role="tablist" style="border-radius: 0;">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold border-0 py-3 px-4 position-relative" 
                                        id="home-tab" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#home-players" 
                                        type="button" 
                                        role="tab" 
                                        aria-controls="home-players" 
                                        aria-selected="true"
                                        style="border-radius: 0;"
                                        data-home-class="{{ $homeTabClass }}"
                                        data-away-class="{{ $awayTabClass }}">
                                    <i class="fas fa-home me-2"></i>
                                    <span class="d-block fw-bold">{{ $overview['homeTeam'] }}</span>
                                    <small class="text-muted">{{ count($homePlayers) }} players</small>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold border-0 py-3 px-4" 
                                        id="away-tab" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#away-players" 
                                        type="button" 
                                        role="tab" 
                                        aria-controls="away-players" 
                                        aria-selected="false"
                                        style="border-radius: 0;"
                                        data-home-class="{{ $homeTabClass }}"
                                        data-away-class="{{ $awayTabClass }}">
                                    <i class="fas fa-plane me-2"></i>
                                    <span class="d-block fw-bold">{{ $overview['awayTeam'] }}</span>
                                    <small class="text-muted">{{ count($awayPlayers) }} players</small>
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="playerTabContent" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                            
                            <div class="tab-pane fade show active" id="home-players" role="tabpanel" aria-labelledby="home-tab">
                                @if(count($homePlayers) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover table-bordered align-middle mb-0" style="font-size: 0.85rem;">
                                            <thead class="table-dark text-center text-uppercase">
                                                <tr>
                                                    <th class="py-2">#</th>
                                                    <th class="py-2 text-start">Player</th>
                                                    <th class="py-2">Pos</th>
                                                    <th class="py-2 bg-secondary">PTS</th>
                                                    <th class="py-2">FG</th>
                                                    <th class="py-2 text-muted">%</th>
                                                    <th class="py-2">2PT</th>
                                                    <th class="py-2 text-muted">%</th>
                                                    <th class="py-2">3PT</th>
                                                    <th class="py-2 text-muted">%</th>
                                                    <th class="py-2">FT</th>
                                                    <th class="py-2 text-muted">%</th>
                                                    <th class="py-2">AST</th>
                                                    <th class="py-2">REB</th>
                                                    <th class="py-2">STL</th>
                                                    <th class="py-2">BLK</th>
                                                    <th class="py-2">TO</th>
                                                    <th class="py-2">PF</th>
                                                    <th class="py-2 bg-primary">PIR</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($homePlayers as $player)
                                                @php $s = $player['stats']; @endphp
                                                <tr class="text-center">
                                                    <td class="fw-bold">{{ $player['jerseyNumber'] }}</td>
                                                    <td class="text-start fw-bold text-nowrap">{{ $player['name'] }}</td>
                                                    <td>{{ $player['position'] }}</td>
                                                    
                                                    {{-- POINTS (Highlighted) --}}
                                                    <td class="fw-bold bg-light border-start border-end">{{ $s['PTS'] }}</td>
                                                    
                                                    {{-- FG --}}
                                                    <td>{{ $s['FG_M'] }}/{{ $s['FG_A'] }}</td>
                                                    <td class="text-muted small">{{ $s['FG%'] }}</td>
                                                    
                                                    {{-- 2PT --}}
                                                    <td>{{ $s['2PT_M'] }}/{{ $s['2PT_A'] }}</td>
                                                    <td class="text-muted small">{{ $s['2PT%'] }}</td>
                                                    
                                                    {{-- 3PT --}}
                                                    <td>{{ $s['3PT_M'] }}/{{ $s['3PT_A'] }}</td>
                                                    <td class="text-muted small">{{ $s['3PT%'] }}</td>
                                                    
                                                    {{-- FT --}}
                                                    <td>{{ $s['FT_M'] }}/{{ $s['FT_A'] }}</td>
                                                    <td class="text-muted small">{{ $s['FT%'] }}</td>
                                                    
                                                    {{-- OTHER STATS --}}
                                                    <td class="{{ $s['AST'] > 0 ? 'fw-bold text-dark' : 'text-muted' }}">{{ $s['AST'] }}</td>
                                                    <td class="{{ $s['REB'] > 0 ? 'fw-bold text-dark' : 'text-muted' }}">{{ $s['REB'] }}</td>
                                                    <td class="{{ $s['STL'] > 0 ? 'fw-bold text-dark' : 'text-muted' }}">{{ $s['STL'] }}</td>
                                                    <td class="{{ $s['BLK'] > 0 ? 'fw-bold text-dark' : 'text-muted' }}">{{ $s['BLK'] }}</td>
                                                    <td class="{{ $s['TO'] > 0 ? 'text-danger' : 'text-muted' }}">{{ $s['TO'] }}</td>
                                                    <td class="{{ $s['FOUL'] > 0 ? 'text-danger' : 'text-muted' }}">{{ $s['FOUL'] }}</td>
                                                    
                                                    {{-- PIR (Highlighted) --}}
                                                    <td class="fw-bold text-white {{ $s['PIR'] >= 20 ? 'bg-success' : ($s['PIR'] >= 10 ? 'bg-info' : 'bg-secondary') }}">
                                                        {{ $s['PIR'] }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <div class="mb-4">
                                            <i class="fas fa-user-slash display-1 text-muted opacity-50"></i>
                                        </div>
                                        <h4 class="text-muted fw-light">No {{ $overview['homeTeam'] }} Players</h4>
                                        <p class="text-muted">No player statistics available for this team</p>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="tab-pane fade" id="away-players" role="tabpanel" aria-labelledby="away-tab">
                                @if(count($awayPlayers) > 0)
                                    <div class="table-responsive p-3">
                                        <table class="table table-striped table-hover table-bordered shadow-sm bg-white align-middle">
                                            <thead class="table-dark text-uppercase">
                                                <tr>
                                                    <th scope="col" style="width: 5%;">#</th>
                                                    <th scope="col" style="width: 30%;">Name</th>
                                                    <th scope="col" style="width: 10%;">Pos</th>
                                                    <th scope="col" class="text-center" style="width: 10%;">PTS</th>
                                                    <th scope="col" class="text-center" style="width: 10%;">3PT</th>
                                                    <th scope="col" class="text-center" style="width: 10%;">2PT</th>
                                                    <th scope="col" class="text-center" style="width: 10%;">FT</th>
                                                    <th scope="col" style="width: 15%;">Other</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($awayPlayers as $player)
                                                <tr>
                                                    <th scope="row">{{ $player['jerseyNumber'] ?? '-' }}</th>
                                                    <td class="fw-bold">{{ $player['name'] }}</td>
                                                    <td>{{ $player['position'] ?? '-' }}</td>
                                                    <td class="fw-bold text-center fs-5">{{ $player['points'] }}</td>
                                                    <td class="text-center">{{ $player['shots']['3PT'] }}</td>
                                                    <td class="text-center">{{ $player['shots']['2PT'] }}</td>
                                                    <td class="text-center">{{ $player['shots']['1PT'] }}</td>
                                                    <td class="small">
                                                        @if($player['age'])
                                                            <div>Age: {{ $player['age'] }}</div>
                                                        @endif
                                                        @if($player['heightWeightDisplay'])
                                                            <div>{{ $player['heightWeightDisplay'] }}</div>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <div class="mb-4">
                                            <i class="fas fa-user-slash display-1 text-muted opacity-50"></i>
                                        </div>
                                        <h4 class="text-muted fw-light">No {{ $overview['awayTeam'] }} Players</h4>
                                        <p class="text-muted">No player statistics available for this team</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-chart-bar display-1 text-muted opacity-50"></i>
                            </div>
                            <h4 class="text-muted fw-light">No Player Statistics Available</h4>
                            <p class="text-muted">Player statistics will appear here once the game data is loaded</p>
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
                                // Dynamic styling based on event type with better contrast
                                if ($event['type'] === 'score') {
                                    $eventBgClass = 'bg-white border-start border-success border-4';
                                    $eventBadgeClass = 'bg-success';
                                    $eventIcon = 'fas fa-basketball-ball text-success';
                                } elseif (in_array($event['type'], ['shot_miss', 'free_throw_miss'])) {
                                    $eventBgClass = 'bg-white border-start border-warning border-4';
                                    $eventBadgeClass = 'bg-warning text-dark';
                                    $eventIcon = 'fas fa-times-circle text-warning';
                                } elseif (in_array($event['type'], ['foul', 'technical_foul'])) {
                                    $eventBgClass = 'bg-white border-start border-danger border-4';
                                    $eventBadgeClass = 'bg-danger';
                                    $eventIcon = 'fas fa-exclamation-triangle text-danger';
                                } elseif ($event['type'] === 'timeout') {
                                    $eventBgClass = 'bg-white border-start border-info border-4';
                                    $eventBadgeClass = 'bg-info';
                                    $eventIcon = 'fas fa-pause-circle text-info';
                                } elseif (in_array($event['type'], ['quarter_start', 'quarter_end', 'game_start', 'game_end'])) {
                                    $eventBgClass = 'bg-white border-start border-secondary border-4';
                                    $eventBadgeClass = 'bg-secondary';
                                    $eventIcon = 'fas fa-flag text-secondary';
                                } else {
                                    $eventBgClass = 'bg-white border-start border-secondary border-4';
                                    $eventBadgeClass = 'bg-secondary';
                                    $eventIcon = 'fas fa-circle text-secondary';
                                }
                                $eventTextClass = 'text-dark';
                            @endphp
                            <div class="d-flex mb-3 p-3 {{ $eventBgClass }} rounded shadow-sm">
                                <div class="flex-shrink-0">
                                    <span class="badge {{ $eventBadgeClass }} fw-bold">
                                        {{ $event['quarter'] }}
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="{{ $eventIcon }} me-2 {{ $eventTextClass }}"></i>
                                        <div class="fw-bold {{ $eventTextClass }}">
                                            @if($event['type'] === 'score')
                                                {{ $event['player'] }} ({{ $event['team'] }})
                                            @else
                                                {{ $event['description'] }}
                                            @endif
                                        </div>
                                    </div>
                                    <small class="{{ $eventTextClass }} fw-semibold">
                                        @if($event['type'] === 'score')
                                            {{-- SAFE CHECK: Use default values if keys are missing --}}
                                            {{ $event['shotType'] ?? '2PT' }} - {{ $event['points'] ?? 0 }} points
                                        @else
                                            {{ ucfirst(str_replace('_', ' ', $event['type'] ?? 'Event')) }}
                                        @endif
                                    </small>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="fw-bold {{ $eventTextClass }}">{{ $event['homeScore'] ?? 0 }} - {{ $event['awayScore'] ?? 0 }}</div>
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
    document.addEventListener('DOMContentLoaded', function() {
        const homeTab = document.getElementById('home-tab');
        const awayTab = document.getElementById('away-tab');
        
        // Set initial classes
        homeTab.className = 'nav-link active fw-bold border-0 py-3 px-4 position-relative ' + homeTab.getAttribute('data-home-class');
        awayTab.className = 'nav-link fw-bold border-0 py-3 px-4 ' + awayTab.getAttribute('data-away-class');
        
        // Add event listeners
        homeTab.addEventListener('shown.bs.tab', function() {
            this.className = 'nav-link active fw-bold border-0 py-3 px-4 position-relative ' + this.getAttribute('data-home-class');
            awayTab.className = 'nav-link fw-bold border-0 py-3 px-4 ' + awayTab.getAttribute('data-away-class');
        });
        
        awayTab.addEventListener('shown.bs.tab', function() {
            this.className = 'nav-link active fw-bold border-0 py-3 px-4 position-relative ' + this.getAttribute('data-away-class');
            homeTab.className = 'nav-link fw-bold border-0 py-3 px-4 ' + homeTab.getAttribute('data-home-class');
        });
    });
</script>
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

    // Shot Analysis Chart - Updated to show made vs missed shots
    const shotCtx = document.getElementById('shotChart').getContext('2d');
    const shotChart = new Chart(shotCtx, {
        type: 'bar',
        data: {
            labels: ['3PT', '2PT', 'FT'],
            datasets: [{
                label: '{{ $overview['homeTeam'] }} Made',
                data: [
                    {{ $overview['shotAnalysis']['home']['made']['3PT'] }},
                    {{ $overview['shotAnalysis']['home']['made']['2PT'] }},
                    {{ $overview['shotAnalysis']['home']['made']['1PT'] }}
                ],
                backgroundColor: isHomeWinner ? 'rgba(40, 167, 69, 0.8)' : (isTie ? 'rgba(255, 193, 7, 0.8)' : 'rgba(220, 53, 69, 0.8)'),
                borderColor: isHomeWinner ? 'rgba(40, 167, 69, 1)' : (isTie ? 'rgba(255, 193, 7, 1)' : 'rgba(220, 53, 69, 1)'),
                borderWidth: 1
            }, {
                label: '{{ $overview['homeTeam'] }} Missed',
                data: [
                    {{ $overview['shotAnalysis']['home']['missed']['3PT'] }},
                    {{ $overview['shotAnalysis']['home']['missed']['2PT'] }},
                    {{ $overview['shotAnalysis']['home']['missed']['1PT'] }}
                ],
                backgroundColor: isHomeWinner ? 'rgba(40, 167, 69, 0.3)' : (isTie ? 'rgba(255, 193, 7, 0.3)' : 'rgba(220, 53, 69, 0.3)'),
                borderColor: isHomeWinner ? 'rgba(40, 167, 69, 0.5)' : (isTie ? 'rgba(255, 193, 7, 0.5)' : 'rgba(220, 53, 69, 0.5)'),
                borderWidth: 1
            }, {
                label: '{{ $overview['awayTeam'] }} Made',
                data: [
                    {{ $overview['shotAnalysis']['away']['made']['3PT'] }},
                    {{ $overview['shotAnalysis']['away']['made']['2PT'] }},
                    {{ $overview['shotAnalysis']['away']['made']['1PT'] }}
                ],
                backgroundColor: isAwayWinner ? 'rgba(40, 167, 69, 0.8)' : (isTie ? 'rgba(255, 193, 7, 0.8)' : 'rgba(220, 53, 69, 0.8)'),
                borderColor: isAwayWinner ? 'rgba(40, 167, 69, 1)' : (isTie ? 'rgba(255, 193, 7, 1)' : 'rgba(220, 53, 69, 1)'),
                borderWidth: 1
            }, {
                label: '{{ $overview['awayTeam'] }} Missed',
                data: [
                    {{ $overview['shotAnalysis']['away']['missed']['3PT'] }},
                    {{ $overview['shotAnalysis']['away']['missed']['2PT'] }},
                    {{ $overview['shotAnalysis']['away']['missed']['1PT'] }}
                ],
                backgroundColor: isAwayWinner ? 'rgba(40, 167, 69, 0.3)' : (isTie ? 'rgba(255, 193, 7, 0.3)' : 'rgba(220, 53, 69, 0.3)'),
                borderColor: isAwayWinner ? 'rgba(40, 167, 69, 0.5)' : (isTie ? 'rgba(255, 193, 7, 0.5)' : 'rgba(220, 53, 69, 0.5)'),
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
                x: {
                    stacked: false,
                    ticks: {
                        color: '#333'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                y: {
                    stacked: false,
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
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
                            size: 10,
                            weight: 'bold'
                        },
                        boxWidth: 12
                    }
                },
                title: {
                    display: true,
                    text: 'Shot Attempts (Made vs Missed)',
                    color: '#333',
                    font: {
                        size: 12,
                        weight: 'bold'
                    }
                }
            }
        }
    });

    
</script>
@endpush
@endsection