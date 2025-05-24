<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\Request;

class GameOverviewController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase->getDatabase();
    }

    public function index(Request $request, $gameId = null)
    {
        // Use route parameter if provided, otherwise fall back to query parameter or default
        $gameId = $gameId ?? $request->query('game_id', '166c8286');
        
        // Get game data from Firebase
        $gameData = $this->firebase->getReference('game_sessions/' . $gameId)->getValue();
        
        if (!$gameData) {
            return redirect()->back()->with('error', 'Game not found');
        }

        $overview = $this->processGameData($gameData);
        $overview['gameId'] = $gameId;

        return view('pages.game-overview', compact('overview'));
    }

    public function listGames()
    {
        // Fetch all game sessions from Firebase
        $games = $this->firebase->getReference('game_sessions')->getValue();
        
        return view('pages.game-list', compact('games'));
    }

    private function processGameData($gameData)
    {
        $gameState = $gameData['game_state'] ?? [];
        $scoringEvents = $gameData['game_logs']['scoring_events'] ?? [];
        $matchLogs = $gameData['game_logs']['match_logs'] ?? [];

        // Basic game info
        $overview = [
            'sessionName' => $gameState['sessionName'] ?? 'Unknown Game',
            'homeTeam' => $gameState['homeTeamName'] ?? 'HOME',
            'awayTeam' => $gameState['awayTeamName'] ?? 'AWAY',
            'homeScore' => $gameState['homeScore'] ?? 0,
            'awayScore' => $gameState['awayScore'] ?? 0,
            'quarter' => $gameState['quarter'] ?? 'Q1',
            'isCompleted' => $gameState['isMatchEnded'] ?? false,
            'homeFouls' => $gameState['homeFouls'] ?? 0,
            'awayFouls' => $gameState['awayFouls'] ?? 0,
            'homeTimeouts' => $gameState['homeTimeouts'] ?? 0,
            'awayTimeouts' => $gameState['awayTimeouts'] ?? 0,
        ];

        // Process scoring events and get shot analysis first (needed for team comparison)
        $overview['scoringBreakdown'] = $this->processScoringEvents($scoringEvents);
        $overview['shotAnalysis'] = $this->processShotAnalysis($scoringEvents, $matchLogs);
        $overview['quarterPerformance'] = $this->processQuarterPerformance($scoringEvents);
        $overview['playerStats'] = $this->processPlayerStats($scoringEvents);
        $overview['teamComparison'] = $this->processTeamComparison($scoringEvents, $overview);
        $overview['gameTimeline'] = $this->processGameTimeline($scoringEvents);

        return $overview;
    }

    private function processScoringEvents($scoringEvents)
    {
        $breakdown = [
            'home' => ['3PT' => 0, '2PT' => 0, '1PT' => 0, 'total' => 0],
            'away' => ['3PT' => 0, '2PT' => 0, '1PT' => 0, 'total' => 0]
        ];

        foreach ($scoringEvents as $event) {
            $team = strtolower($event['team'] ?? 'home');
            $shotType = $event['shotType'] ?? '2PT';
            $points = $event['points'] ?? 0;

            // Map '1PT' to '1PT' for consistency
            if ($shotType === '1PT') {
                $shotType = '1PT';
            }

            if (isset($breakdown[$team]) && in_array($shotType, ['3PT', '2PT', '1PT'])) {
                $breakdown[$team][$shotType] += 1;
                $breakdown[$team]['total'] += $points;
            }
        }

        return $breakdown;
    }

    private function processQuarterPerformance($scoringEvents)
    {
        $quarters = [
            'home' => ['Q1' => 0, 'Q2' => 0, 'Q3' => 0, 'Q4' => 0],
            'away' => ['Q1' => 0, 'Q2' => 0, 'Q3' => 0, 'Q4' => 0]
        ];

        foreach ($scoringEvents as $event) {
            $team = strtolower($event['team'] ?? 'home');
            $quarter = $event['quarter'] ?? 'Q1';
            $points = $event['points'] ?? 0;

            if (isset($quarters[$team]) && isset($quarters[$team][$quarter])) {
                $quarters[$team][$quarter] += $points;
            }
        }

        return $quarters;
    }

    private function processPlayerStats($scoringEvents)
    {
        $players = [];

        foreach ($scoringEvents as $event) {
            $player = $event['player'] ?? 'Unknown';
            $team = $event['team'] ?? 'HOME';
            $points = $event['points'] ?? 0;
            $shotType = $event['shotType'] ?? '2PT';

            // Map '1PT' to '1PT' for consistency
            if ($shotType === '1PT') {
                $shotType = '1PT';
            }

            if (!isset($players[$player])) {
                $players[$player] = [
                    'name' => $player,
                    'team' => $team,
                    'points' => 0,
                    'shots' => ['3PT' => 0, '2PT' => 0, '1PT' => 0],
                    'totalShots' => 0
                ];
            }

            $players[$player]['points'] += $points;
            $players[$player]['shots'][$shotType]++;
            $players[$player]['totalShots']++;
        }

        // Sort by points
        uasort($players, function($a, $b) {
            return $b['points'] - $a['points'];
        });

        return array_values($players);
    }

    private function processTeamComparison($scoringEvents, $overview)
    {
        $home = ['name' => $overview['homeTeam'], 'score' => $overview['homeScore']];
        $away = ['name' => $overview['awayTeam'], 'score' => $overview['awayScore']];

        // Get successful shots from scoring breakdown
        $homeShots = $overview['scoringBreakdown']['home'];
        $awayShots = $overview['scoringBreakdown']['away'];

        // Calculate total successful shots (made shots)
        $home['madeShots'] = ($homeShots['3PT'] ?? 0) + ($homeShots['2PT'] ?? 0) + ($homeShots['1PT'] ?? 0);
        $away['madeShots'] = ($awayShots['3PT'] ?? 0) + ($awayShots['2PT'] ?? 0) + ($awayShots['1PT'] ?? 0);

        // Get total shot attempts from shot analysis
        $home['totalShots'] = $overview['shotAnalysis']['home']['total'] ?? $home['madeShots'];
        $away['totalShots'] = $overview['shotAnalysis']['away']['total'] ?? $away['madeShots'];

        // Calculate shooting efficiency as (made shots / total attempts) * 100
        $home['efficiency'] = $home['totalShots'] > 0 ? round(($home['madeShots'] / $home['totalShots']) * 100, 1) : 0;
        $away['efficiency'] = $away['totalShots'] > 0 ? round(($away['madeShots'] / $away['totalShots']) * 100, 1) : 0;

        return compact('home', 'away');
    }

    private function processGameTimeline($scoringEvents)
    {
        $timeline = [];
        $homeScore = 0;
        $awayScore = 0;

        foreach ($scoringEvents as $event) {
            $team = strtolower($event['team'] ?? 'home');
            $points = $event['points'] ?? 0;
            
            if ($team === 'home') {
                $homeScore += $points;
            } else {
                $awayScore += $points;
            }

            $timeline[] = [
                'player' => $event['player'] ?? 'Unknown',
                'team' => $event['team'] ?? 'HOME',
                'quarter' => $event['quarter'] ?? 'Q1',
                'shotType' => $event['shotType'] ?? '2PT',
                'points' => $points,
                'homeScore' => $homeScore,
                'awayScore' => $awayScore,
                'timestamp' => $event['timestamp'] ?? 0
            ];
        }

        return $timeline;
    }

    private function processShotAnalysis($scoringEvents, $matchLogs = [])
    {
        $analysis = [
            'home' => ['made' => 0, 'total' => 0, 'percentage' => 0],
            'away' => ['made' => 0, 'total' => 0, 'percentage' => 0]
        ];

        // Count made shots from scoring events
        foreach ($scoringEvents as $event) {
            $team = strtolower($event['team'] ?? 'home');
            
            if (isset($analysis[$team])) {
                $analysis[$team]['made']++;
            }
        }

        // If you have miss events in matchLogs, count them here
        // This is assuming miss events are stored separately in match_logs
        foreach ($matchLogs as $log) {
            if (isset($log['eventType']) && $log['eventType'] === 'shot_miss') {
                $team = strtolower($log['team'] ?? 'home');
                if (isset($analysis[$team])) {
                    // Don't increment made shots for misses
                }
            }
        }

        // For now, if no miss data is available, assume total attempts = made shots
        // You'll need to modify this based on your actual data structure
        foreach ($analysis as $team => &$stats) {
            if ($stats['total'] == 0) {
                $stats['total'] = $stats['made']; // This assumes 100% efficiency if no miss data
            }
            
            if ($stats['total'] > 0) {
                $stats['percentage'] = round(($stats['made'] / $stats['total']) * 100, 1);
            }
        }

        return $analysis;
    }
}