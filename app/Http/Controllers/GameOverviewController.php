<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $gamesData = $this->firebase->getReference('game_sessions')->getValue();
        
        // Handle case where no games exist or Firebase returns null
        if (!$gamesData || !is_array($gamesData)) {
            $games = [];
        } else {
            // Ensure each game has the expected structure
            $games = [];
            foreach ($gamesData as $gameId => $gameData) {
                // Check if game_state exists, if not create a default structure
                if (!isset($gameData['game_state'])) {
                    $games[$gameId] = [
                        'game_state' => [
                            'sessionName' => 'Unknown Game',
                            'homeTeamName' => 'HOME',
                            'awayTeamName' => 'AWAY',
                            'homeScore' => 0,
                            'awayScore' => 0,
                            'quarter' => 'Q1',
                            'isMatchEnded' => false
                        ]
                    ];
                } else {
                    $games[$gameId] = $gameData;
                }
            }
        }
        
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
        $overview['playerStats'] = $this->processPlayerStats($scoringEvents, $gameData);
        $overview['teamComparison'] = $this->processTeamComparison($scoringEvents, $overview);
        $overview['gameTimeline'] = $this->processCompleteGameTimeline($scoringEvents, $matchLogs);

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

    private function processPlayerStats($scoringEvents, $gameData = null)
    {
        $players = [];
        
        // Get player roster data if available
        $playerRoster = [];
        if ($gameData && isset($gameData['players'])) {
            foreach (['HOME', 'AWAY'] as $teamNode) {
                if (isset($gameData['players'][$teamNode])) {
                    foreach ($gameData['players'][$teamNode] as $playerId => $playerData) {
                        // Use player name as key for easier lookup
                        $playerName = $playerData['name'] ?? 'Unknown';
                        $playerRoster[$playerName] = [
                            'team' => $teamNode, // Use the Firebase node name (HOME/AWAY)
                            'jerseyNumber' => $playerData['jerseyNumber'] ?? null,
                            'position' => $playerData['position'] ?? null,
                            'height' => $playerData['height'] ?? null,
                            'weight' => $playerData['weight'] ?? null,
                            'age' => $playerData['age'] ?? null,
                            'heightWeightDisplay' => $playerData['heightWeightDisplay'] ?? null
                        ];
                    }
                }
            }
        }

        // Process scoring events
        foreach ($scoringEvents as $event) {
            $playerName = $event['player'] ?? 'Unknown';
            $eventTeam = $event['team'] ?? 'HOME'; // This should be HOME or AWAY from scoring events
            $points = $event['points'] ?? 0;
            $shotType = $event['shotType'] ?? '2PT';

            // Initialize player if not exists
            if (!isset($players[$playerName])) {
                // Get roster info if available, otherwise use defaults
                $rosterInfo = $playerRoster[$playerName] ?? [
                    'team' => $eventTeam, // Use event team as fallback
                    'jerseyNumber' => null,
                    'position' => null,
                    'height' => null,
                    'weight' => null,
                    'age' => null,
                    'heightWeightDisplay' => null
                ];

                $players[$playerName] = [
                    'name' => $playerName,
                    'team' => $rosterInfo['team'], // This should be HOME or AWAY
                    'points' => 0,
                    'shots' => ['3PT' => 0, '2PT' => 0, '1PT' => 0],
                    'totalShots' => 0,
                    'jerseyNumber' => $rosterInfo['jerseyNumber'],
                    'position' => $rosterInfo['position'],
                    'height' => $rosterInfo['height'],
                    'weight' => $rosterInfo['weight'],
                    'age' => $rosterInfo['age'],
                    'heightWeightDisplay' => $rosterInfo['heightWeightDisplay']
                ];
            }

            // Add points and shots
            $players[$playerName]['points'] += $points;
            
            // Ensure shot type exists in the shots array
            if (!isset($players[$playerName]['shots'][$shotType])) {
                $players[$playerName]['shots'][$shotType] = 0;
            }
            
            $players[$playerName]['shots'][$shotType]++;
            $players[$playerName]['totalShots']++;
        }

        // Add players from roster who didn't score (optional)
        foreach ($playerRoster as $playerName => $rosterData) {
            if (!isset($players[$playerName])) {
                $players[$playerName] = [
                    'name' => $playerName,
                    'team' => $rosterData['team'], // HOME or AWAY
                    'points' => 0,
                    'shots' => ['3PT' => 0, '2PT' => 0, '1PT' => 0],
                    'totalShots' => 0,
                    'jerseyNumber' => $rosterData['jerseyNumber'],
                    'position' => $rosterData['position'],
                    'height' => $rosterData['height'],
                    'weight' => $rosterData['weight'],
                    'age' => $rosterData['age'],
                    'heightWeightDisplay' => $rosterData['heightWeightDisplay']
                ];
            }
        }

        // Sort by points (descending)
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

        // Get total shot attempts from shot analysis - FIX: Access the 'total' key correctly
        $home['totalShots'] = $overview['shotAnalysis']['home']['total']['total'] ?? $home['madeShots'];
        $away['totalShots'] = $overview['shotAnalysis']['away']['total']['total'] ?? $away['madeShots'];

        // Calculate shooting efficiency as (made shots / total attempts) * 100
        $home['efficiency'] = $home['totalShots'] > 0 ? round(($home['madeShots'] / $home['totalShots']) * 100, 1) : 0;
        $away['efficiency'] = $away['totalShots'] > 0 ? round(($away['madeShots'] / $away['totalShots']) * 100, 1) : 0;

        return compact('home', 'away');
    }

    private function processCompleteGameTimeline($scoringEvents, $matchLogs)
    {
        $timeline = [];

        // Process scoring events
        foreach ($scoringEvents as $event) {
            $timeline[] = [
                'type' => 'score',
                'player' => $event['player'] ?? 'Unknown',
                'team' => $event['team'] ?? 'HOME',
                'quarter' => $event['quarter'] ?? 'Q1',
                'shotType' => $event['shotType'] ?? '2PT',
                'points' => $event['points'] ?? 0,
                'timestamp' => $event['timestamp'] ?? 0,
                'description' => ($event['player'] ?? 'Unknown') . ' scored ' . ($event['points'] ?? 0) . ' points (' . ($event['shotType'] ?? '2PT') . ')'
            ];
        }

        // Process match logs (non-scoring events)
        foreach ($matchLogs as $log) {
            $statType = $log['stat_type'] ?? 'unknown';
            $player = $log['player'] ?? 'Unknown';
            $team = $log['team'] ?? 'Unknown';
            $quarter = $log['quarter'] ?? 'Q1';
            $timestamp = $log['timestamp'] ?? 0;

            // Skip scoring events as they're already processed above
            if (in_array($statType, ['3PT', '2PT', '1PT'])) {
                continue;
            }

            // Map stat_type to event type and create description
            $eventType = $this->mapStatTypeToEventType($statType);
            $description = $this->getEventDescription($eventType, $log);
            
            $timeline[] = [
                'type' => $eventType,
                'player' => $player,
                'team' => $team,
                'quarter' => $quarter,
                'timestamp' => $timestamp,
                'description' => $description,
                'details' => $log // Include all log details for reference
            ];
        }

        // Sort by timestamp to get chronological order
        usort($timeline, function($a, $b) {
            return ($a['timestamp'] ?? 0) - ($b['timestamp'] ?? 0);
        });

        // Calculate running scores for timeline display
        $homeScore = 0;
        $awayScore = 0;
        
        foreach ($timeline as &$event) {
            if ($event['type'] === 'score') {
                $team = strtolower($event['team']);
                $points = $event['points'] ?? 0;
                
                if ($team === 'home') {
                    $homeScore += $points;
                } else {
                    $awayScore += $points;
                }
            }
            
            // Set current score for all events
            $event['homeScore'] = $homeScore;
            $event['awayScore'] = $awayScore;
        }

        return $timeline;
    }

    private function mapStatTypeToEventType($statType)
    {
        $mapping = [
            'ASSIST' => 'assist',
            'REBOUND' => 'rebound',
            'STEAL' => 'steal',
            'BLOCK' => 'block',
            'TURNOVER' => 'turnover',
            'FOUL' => 'foul',
            '3PT MISS' => 'shot_miss',
            '2PT MISS' => 'shot_miss',
            '1PT MISS' => 'free_throw_miss',
            // Add more mappings as needed
        ];

        return $mapping[$statType] ?? strtolower(str_replace(' ', '_', $statType));
    }

    private function getEventDescription($eventType, $log)
    {
        $player = $log['player'] ?? 'Unknown';
        $team = $log['team'] ?? '';
        $statType = $log['stat_type'] ?? '';
        
        switch ($eventType) {
            case 'shot_miss':
                // Determine shot type from stat_type
                if (strpos($statType, '3PT') !== false) {
                    return $player . ' missed a 3-pointer';
                } elseif (strpos($statType, '2PT') !== false) {
                    return $player . ' missed a 2-pointer';
                }
                return $player . ' missed a shot';
            case 'free_throw_miss':
                return $player . ' missed free throw';
            case 'foul':
                return $player . ' committed a foul';
            case 'foul_received':
                return $player . ' was fouled';
            case 'timeout':
                return $team . ' called a timeout';
            case 'substitution':
                return 'Substitution: ' . $player . ' ' . ($log['action'] ?? '');
            case 'quarter_start':
                return 'Quarter ' . ($log['quarter'] ?? '') . ' started';
            case 'quarter_end':
                return 'Quarter ' . ($log['quarter'] ?? '') . ' ended';
            case 'game_start':
                return 'Game started';
            case 'game_end':
                return 'Game ended';
            case 'technical_foul':
                return $player . ' received a technical foul';
            case 'rebound':
                return $player . ' got a rebound';
            case 'assist':
                return $player . ' made an assist';
            case 'steal':
                return $player . ' made a steal';
            case 'block':
                return $player . ' made a block';
            case 'turnover':
                return $player . ' committed a turnover';
            default:
                return $player . ' - ' . ucfirst(str_replace('_', ' ', $eventType));
        }
    }

    private function processShotAnalysis($scoringEvents, $matchLogs = [])
    {
        $analysis = [
            'home' => [
                'made' => ['3PT' => 0, '2PT' => 0, '1PT' => 0, 'total' => 0],
                'missed' => ['3PT' => 0, '2PT' => 0, '1PT' => 0, 'total' => 0],
                'total' => ['3PT' => 0, '2PT' => 0, '1PT' => 0, 'total' => 0],
                'percentage' => ['3PT' => 0, '2PT' => 0, '1PT' => 0, 'overall' => 0]
            ],
            'away' => [
                'made' => ['3PT' => 0, '2PT' => 0, '1PT' => 0, 'total' => 0],
                'missed' => ['3PT' => 0, '2PT' => 0, '1PT' => 0, 'total' => 0],
                'total' => ['3PT' => 0, '2PT' => 0, '1PT' => 0, 'total' => 0],
                'percentage' => ['3PT' => 0, '2PT' => 0, '1PT' => 0, 'overall' => 0]
            ]
        ];

        // Count made shots from scoring events
        foreach ($scoringEvents as $event) {
            $team = strtolower($event['team'] ?? 'home');
            $shotType = $event['shotType'] ?? '2PT';
            
            if (isset($analysis[$team])) {
                $analysis[$team]['made'][$shotType]++;
                $analysis[$team]['made']['total']++;
            }
        }

        // Count missed shots from match logs
        foreach ($matchLogs as $log) {
            $statType = $log['stat_type'] ?? '';
            $team = strtolower($log['team'] ?? 'home');
            
            if (strpos($statType, 'MISS') !== false && isset($analysis[$team])) {
                if (strpos($statType, '3PT') !== false) {
                    $analysis[$team]['missed']['3PT']++;
                } elseif (strpos($statType, '2PT') !== false) {
                    $analysis[$team]['missed']['2PT']++;
                } elseif (strpos($statType, '1PT') !== false) {
                    $analysis[$team]['missed']['1PT']++;
                }
                $analysis[$team]['missed']['total']++;
            }
        }

        // Calculate totals and percentages
        foreach (['home', 'away'] as $team) {
            foreach (['3PT', '2PT', '1PT'] as $shotType) {
                $made = $analysis[$team]['made'][$shotType];
                $missed = $analysis[$team]['missed'][$shotType];
                $total = $made + $missed;
                
                $analysis[$team]['total'][$shotType] = $total;
                $analysis[$team]['percentage'][$shotType] = $total > 0 ? round(($made / $total) * 100, 1) : 0;
            }
            
            // Overall totals
            $analysis[$team]['total']['total'] = $analysis[$team]['made']['total'] + $analysis[$team]['missed']['total'];
            $analysis[$team]['percentage']['overall'] = $analysis[$team]['total']['total'] > 0 ? 
                round(($analysis[$team]['made']['total'] / $analysis[$team]['total']['total']) * 100, 1) : 0;
        }

        return $analysis;
    }

    public function deleteGame($gameId)
    {
        try {
            // Delete the game session from Firebase
            $this->firebase->getReference('game_sessions/' . $gameId)->remove();
            
            return response()->json([
                'success' => true,
                'message' => 'Game session deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete game session: ' . $e->getMessage()
            ], 500);
        }
    }
}