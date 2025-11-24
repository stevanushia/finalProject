<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Game Report - {{ $overview['sessionName'] }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 24px; color: #222; }
        .header p { margin: 5px 0 0; color: #666; }
        
        .score-box { text-align: center; padding: 15px; background-color: #f4f4f4; margin-bottom: 20px; border: 1px solid #ddd; }
        .score-big { font-size: 32px; font-weight: bold; color: #000; }
        .teams { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: center; }
        th { background-color: #333; color: #fff; text-transform: uppercase; font-size: 10px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 10px; border-left: 4px solid #28a745; padding-left: 10px; color: #333; }
        .text-left { text-align: left; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 9px; background: #eee; border: 1px solid #ccc; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $overview['sessionName'] }}</h1>
        <p>Generated on {{ date('M d, Y H:i') }}</p>
    </div>

    <div class="score-box">
        <div class="teams">
            {{ $overview['homeTeam'] }} <span style="color:#888">vs</span> {{ $overview['awayTeam'] }}
        </div>
        <div class="score-big">
            {{ $overview['homeScore'] }} - {{ $overview['awayScore'] }}
        </div>
        <div>
            {{ $overview['isCompleted'] ? 'FINAL' : 'IN PROGRESS (' . $overview['quarter'] . ')' }}
        </div>
    </div>

    <div class="section-title">Quarter Scoring</div>
    <table>
        <thead>
            <tr>
                <th>Team</th>
                <th>Q1</th>
                <th>Q2</th>
                <th>Q3</th>
                <th>Q4</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-left fw-bold">{{ $overview['homeTeam'] }}</td>
                <td>{{ $overview['quarterPerformance']['home']['Q1'] }}</td>
                <td>{{ $overview['quarterPerformance']['home']['Q2'] }}</td>
                <td>{{ $overview['quarterPerformance']['home']['Q3'] }}</td>
                <td>{{ $overview['quarterPerformance']['home']['Q4'] }}</td>
                <td><strong>{{ $overview['homeScore'] }}</strong></td>
            </tr>
            <tr>
                <td class="text-left fw-bold">{{ $overview['awayTeam'] }}</td>
                <td>{{ $overview['quarterPerformance']['away']['Q1'] }}</td>
                <td>{{ $overview['quarterPerformance']['away']['Q2'] }}</td>
                <td>{{ $overview['quarterPerformance']['away']['Q3'] }}</td>
                <td>{{ $overview['quarterPerformance']['away']['Q4'] }}</td>
                <td><strong>{{ $overview['awayScore'] }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">{{ $overview['homeTeam'] }} - Player Stats</div>
    @php $homePlayers = collect($overview['playerStats'])->where('team', 'HOME')->sortByDesc('stats.PIR'); @endphp
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th class="text-left">Player</th>
                <th>Pos</th>
                <th>PTS</th>
                <th>FG</th>
                <th>3PT</th>
                <th>FT</th>
                <th>AST</th>
                <th>REB</th>
                <th>STL</th>
                <th>BLK</th>
                <th>TO</th>
                <th>PF</th>
                <th>PIR</th>
            </tr>
        </thead>
        <tbody>
            @foreach($homePlayers as $p)
            @php $s = $p['stats']; @endphp
            <tr>
                <td>{{ $p['jerseyNumber'] }}</td>
                <td class="text-left">{{ $p['name'] }}</td>
                <td>{{ $p['position'] }}</td>
                <td style="background-color: #e8f5e9; font-weight: bold;">{{ $s['PTS'] }}</td>
                <td>{{ $s['FG_M'] }}/{{ $s['FG_A'] }}</td>
                <td>{{ $s['3PT_M'] }}/{{ $s['3PT_A'] }}</td>
                <td>{{ $s['FT_M'] }}/{{ $s['FT_A'] }}</td>
                <td>{{ $s['AST'] }}</td>
                <td>{{ $s['REB'] }}</td>
                <td>{{ $s['STL'] }}</td>
                <td>{{ $s['BLK'] }}</td>
                <td>{{ $s['TO'] }}</td>
                <td>{{ $s['FOUL'] }}</td>
                <td style="background-color: #e3f2fd; font-weight: bold;">{{ $s['PIR'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">{{ $overview['awayTeam'] }} - Player Stats</div>
    @php $awayPlayers = collect($overview['playerStats'])->where('team', 'AWAY')->sortByDesc('stats.PIR'); @endphp
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th class="text-left">Player</th>
                <th>Pos</th>
                <th>PTS</th>
                <th>FG</th>
                <th>3PT</th>
                <th>FT</th>
                <th>AST</th>
                <th>REB</th>
                <th>STL</th>
                <th>BLK</th>
                <th>TO</th>
                <th>PF</th>
                <th>PIR</th>
            </tr>
        </thead>
        <tbody>
            @foreach($awayPlayers as $p)
            @php $s = $p['stats']; @endphp
            <tr>
                <td>{{ $p['jerseyNumber'] }}</td>
                <td class="text-left">{{ $p['name'] }}</td>
                <td>{{ $p['position'] }}</td>
                <td style="background-color: #e8f5e9; font-weight: bold;">{{ $s['PTS'] }}</td>
                <td>{{ $s['FG_M'] }}/{{ $s['FG_A'] }}</td>
                <td>{{ $s['3PT_M'] }}/{{ $s['3PT_A'] }}</td>
                <td>{{ $s['FT_M'] }}/{{ $s['FT_A'] }}</td>
                <td>{{ $s['AST'] }}</td>
                <td>{{ $s['REB'] }}</td>
                <td>{{ $s['STL'] }}</td>
                <td>{{ $s['BLK'] }}</td>
                <td>{{ $s['TO'] }}</td>
                <td>{{ $s['FOUL'] }}</td>
                <td style="background-color: #e3f2fd; font-weight: bold;">{{ $s['PIR'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>