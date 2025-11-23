<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Log; // Import Log facade
use Carbon\Carbon;

class HomeController extends Controller
{
    protected $database;

    public function __construct(FirebaseService $firebase)
    {
        $this->database = $firebase->getDatabase();
    }

    public function index()
    {
        try {
            $currentUid = session('firebase_uid'); // <--- Get User ID

            // 1. Fetch Games
            $games = $this->database->getReference('game_sessions')->getValue() ?? [];
            
            $liveGames = [];
            $recentGames = [];

            foreach ($games as $id => $game) {
                $state = $game['game_state'] ?? [];
                
                // --- SECURITY FILTER ---
                $creatorUid = $state['creatorUid'] ?? null;
                
                // Skip if not my game
                if ($creatorUid !== $currentUid) {
                    continue; 
                }
                
                // --- FIX 1: Safe Log Access (Prevents "end()" error) ---
                $lastLogTimestamp = 0;
                $matchLogs = $game['game_logs']['match_logs'] ?? [];
                
                // Only try to get the last log if the array is valid and not empty
                if (is_array($matchLogs) && !empty($matchLogs)) {
                    $lastLog = end($matchLogs);
                    $lastLogTimestamp = $lastLog['timestamp'] ?? 0;
                }
                // -------------------------------------------------------

                $gameData = [
                    'id' => $id,
                    'name' => $state['sessionName'] ?? 'Untitled Game',
                    'home' => $state['homeTeamName'] ?? 'HOME',
                    'away' => $state['awayTeamName'] ?? 'AWAY',
                    'homeScore' => $state['homeScore'] ?? 0,
                    'awayScore' => $state['awayScore'] ?? 0,
                    'quarter' => $state['quarter'] ?? '-',
                    'timestamp' => $lastLogTimestamp
                ];

                // --- FIX 2: Loose Boolean Check ---
                // Checks if it exists and is "truthy" (true, 1, "true")
                $isMatchEnded = !empty($state['isMatchEnded']); 

                if ($isMatchEnded) {
                    $recentGames[] = $gameData;
                } else {
                    $liveGames[] = $gameData;
                }
            }

            // Sort by timestamp (newest first)
            usort($recentGames, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
            $recentGames = array_slice($recentGames, 0, 5);

            // 2. Fetch Tournaments
            $tournaments = $this->database->getReference('tournaments')->getValue() ?? [];
            $upcomingTournaments = [];

            foreach ($tournaments as $id => $t) {
                if (($t['status'] ?? '') !== 'completed') {
                    $upcomingTournaments[] = [
                        'id' => $id,
                        'name' => $t['name'] ?? 'Tournament',
                        'teams' => $t['participantCount'] ?? 0,
                        'startDate' => $t['startDate'] ?? 0
                    ];
                }
            }
            
            usort($upcomingTournaments, fn($a, $b) => $a['startDate'] <=> $b['startDate']);
            $upcomingTournaments = array_slice($upcomingTournaments, 0, 3);

            return view('pages.home', compact('liveGames', 'recentGames', 'upcomingTournaments'));

        } catch (\Exception $e) {
            // --- FIX 3: Log the error so we know what's wrong ---
            Log::error('HomeController Error: ' . $e->getMessage());
            
            // Return empty arrays so the page still loads
            return view('pages.home', [
                'liveGames' => [], 
                'recentGames' => [], 
                'upcomingTournaments' => []
            ]);
        }
    }
}