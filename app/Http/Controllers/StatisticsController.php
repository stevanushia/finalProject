<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        // Get current user's Firebase UID (adjust based on your auth system)
        $userUid = $this->getCurrentUserUid();
        
        if (!$userUid) {
            return redirect()->route('login')->with('error', 'Please login to view statistics');
        }

        try {
            // Fetch user's game sessions and statistics
            $statistics = $this->getUserStatistics($userUid);
            
            return view('pages.statistics', compact('statistics'));
        } catch (\Exception $e) {
            return back()->with('error', 'Unable to load statistics: ' . $e->getMessage());
        }
    }

    private function getCurrentUserUid()
    {
        // Adjust this based on how you store Firebase UID
        // Option 1: From session
        return session('firebase_uid');
        
        // Option 2: From authenticated user
        // return auth()->user()->firebase_uid;
        
        // Option 3: From request
        // return request()->get('uid');
    }

    private function getUserStatistics($userUid)
    {
        // Firebase Realtime Database URL (replace with your project ID)
        $firebaseUrl = 'https://project-ta-df552-default-rtdb.firebaseio.com';
        
        // Fetch game sessions
        $gameSessionsResponse = Http::get("{$firebaseUrl}/game_sessions.json");
        $gameSessions = $gameSessionsResponse->json() ?? [];

        // Fetch user data
        $userResponse = Http::get("{$firebaseUrl}/users/{$userUid}.json");
        $userData = $userResponse->json() ?? [];

        // Process statistics
        $stats = [
            'user' => $userData,
            'totalGames' => 0,
            'totalPoints' => 0,
            'totalShots' => 0,
            'threePointers' => 0,
            'twoPointers' => 0,
            'freeThrows' => 0,
            'gameHistory' => [],
            'quarterBreakdown' => [
                'Q1' => 0, 'Q2' => 0, 'Q3' => 0, 'Q4' => 0
            ],
            'teamStats' => [
                'HOME' => ['games' => 0, 'points' => 0],
                'AWAY' => ['games' => 0, 'points' => 0]
            ]
        ];

        // Process each game session
        foreach ($gameSessions as $sessionId => $session) {
            if (isset($session['creatorUid']) && $session['creatorUid'] === $userUid) {
                $stats['totalGames']++;
                
                // Process game logs and scoring events
                if (isset($session['game_logs']['scoring_events'])) {
                    foreach ($session['game_logs']['scoring_events'] as $event) {
                        $stats['totalPoints'] += $event['points'] ?? 0;
                        $stats['totalShots']++;
                        
                        // Count shot types
                        $shotType = $event['shotType'] ?? '';
                        switch ($shotType) {
                            case '3PT':
                                $stats['threePointers']++;
                                break;
                            case '2PT':
                                $stats['twoPointers']++;
                                break;
                            case 'FT':
                                $stats['freeThrows']++;
                                break;
                        }
                        
                        // Quarter breakdown
                        $quarter = $event['quarter'] ?? 'Q1';
                        if (isset($stats['quarterBreakdown'][$quarter])) {
                            $stats['quarterBreakdown'][$quarter] += $event['points'] ?? 0;
                        }
                        
                        // Team stats
                        $team = $event['team'] ?? 'HOME';
                        if (isset($stats['teamStats'][$team])) {
                            $stats['teamStats'][$team]['points'] += $event['points'] ?? 0;
                        }
                    }
                }
                
                // Add to game history
                $stats['gameHistory'][] = [
                    'sessionId' => $sessionId,
                    'sessionName' => $session['game_state']['sessionName'] ?? 'Unknown Game',
                    'homeTeam' => $session['game_state']['homeTeamName'] ?? 'HOME',
                    'awayTeam' => $session['game_state']['awayTeamName'] ?? 'AWAY',
                    'homeScore' => $session['game_state']['homeScore'] ?? 0,
                    'awayScore' => $session['game_state']['awayScore'] ?? 0,
                    'quarter' => $session['game_state']['quarter'] ?? 'Q1',
                    'isCompleted' => $session['game_state']['isMatchEnded'] ?? false
                ];
            }
        }

        // Calculate percentages and averages
        $stats['averagePointsPerGame'] = $stats['totalGames'] > 0 ? 
            round($stats['totalPoints'] / $stats['totalGames'], 1) : 0;
        
        $stats['threePointPercentage'] = $stats['totalShots'] > 0 ? 
            round(($stats['threePointers'] / $stats['totalShots']) * 100, 1) : 0;

        return $stats;
    }
}