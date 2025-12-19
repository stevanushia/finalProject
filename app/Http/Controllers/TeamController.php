<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;

class TeamController extends Controller
{
    protected $database;

    public function __construct(FirebaseService $firebase)
    {
        $this->database = $firebase->getDatabase();
    }

    /**
     * List all teams created by the user
     */
    public function index()
    {
        $uid = session('firebase_uid');
        if (!$uid) return redirect('/login');

        // Fetch teams where creatorUid == current user
        $teams = $this->database->getReference('teams')
                    ->getValue();

        return view('teams.index', compact('teams'));
    }

    /**
     * Show Team History (Tournaments & Stats)
     */
    public function show($teamId)
    {
        // 1. Get Master Team Details
        $team = $this->database->getReference("teams/{$teamId}")->getValue();
        if (!$team) return back()->with('error', 'Team not found.');

        $teamName = $team['name']; 

        // 2. Fetch Data
        $allTournaments = $this->database->getReference('tournaments')->getValue();
        $allSessionKeys = $this->database->getReference('game_sessions')->shallow()->getValue(); 

        $history = [];
        // NEW: Initialize detailed stats
        $stats = [
            'tournaments_played' => 0,
            'tournaments_won' => 0,
            'matches_played' => 0, 
            'matches_won' => 0,
            'win_rate' => 0
        ];

        if ($allTournaments) {
            foreach ($allTournaments as $tId => $tData) {
                
                // Check if team participated
                $inTournament = false;
                if (isset($tData['teams'])) {
                    foreach ($tData['teams'] as $tTeam) {
                        if ((isset($tTeam['teamId']) && $tTeam['teamId'] === $teamId) || 
                            ($tTeam['name'] === $teamName)) {
                            $inTournament = true;
                            break;
                        }
                    }
                }

                if (!$inTournament) continue;

                $matchesPlayed = [];
                if (isset($tData['matches'])) {
                    foreach ($tData['matches'] as $mId => $match) {
                        $home = $match['home'] ?? '';
                        $away = $match['away'] ?? '';

                        // If team played in this match
                        if (($home === $teamName || $away === $teamName) && !empty($home) && !empty($away)) {
                            $isHome = ($home === $teamName);
                            $opponent = $isHome ? $away : $home;
                            
                            $myScore = $isHome ? ($match['scoreHome'] ?? 0) : ($match['scoreAway'] ?? 0);
                            $oppScore = $isHome ? ($match['scoreAway'] ?? 0) : ($match['scoreHome'] ?? 0);
                            $scoreDisplay = "{$myScore} - {$oppScore}";

                            $status = $match['status'] ?? 'scheduled';
                            $result = 'Scheduled';
                            $resultColor = 'secondary';
                            
                            $sessionExists = isset($allSessionKeys[$mId]);
                            $isPlayable = false;

                            if ($status === 'completed') {
                                // --- NEW: Increment Match Stats ---
                                $stats['matches_played']++;
                                
                                if (($match['winner'] ?? '') === $teamName) {
                                    $result = 'WON';
                                    $resultColor = 'success';
                                    $stats['matches_won']++; // Increment Wins
                                } else {
                                    $result = 'LOST';
                                    $resultColor = 'danger';
                                }
                                
                                if ($sessionExists) $isPlayable = true;
                            } else {
                                // Handle Live/Scheduled logic (same as before)
                                if ($myScore > 0 || $oppScore > 0) {
                                    $result = 'LIVE';
                                    $resultColor = 'warning text-dark';
                                    if ($sessionExists) $isPlayable = true;
                                } else {
                                    $result = 'VS';
                                    if ($sessionExists) $isPlayable = true;
                                }
                            }

                            $matchesPlayed[] = [
                                'matchId' => $mId, 
                                'round' => $match['round'] ?? 1,
                                'opponent' => $opponent,
                                'result' => $result,
                                'resultColor' => $resultColor,
                                'score' => $scoreDisplay,
                                'isPlayable' => $isPlayable,
                                'status' => $status,
                                'sessionExists' => $sessionExists
                            ];
                        }
                    }
                }

                usort($matchesPlayed, fn($a, $b) => $a['round'] <=> $b['round']);

                // Tournament Stats
                $overallResult = 'Participant';
                if (($tData['status'] ?? '') === 'completed') {
                    if (($tData['winner'] ?? '') === $teamName) {
                        $overallResult = 'Champion';
                        $stats['tournaments_won']++;
                    } else {
                        $overallResult = 'Eliminated';
                    }
                }

                $history[] = [
                    'id' => $tId,
                    'tournamentName' => $tData['name'],
                    'date' => $tData['startDate'],
                    'result' => $overallResult,
                    'matches' => $matchesPlayed 
                ];
                $stats['tournaments_played']++;
            }
        }

        // --- NEW: Calculate Win Rate ---
        if ($stats['matches_played'] > 0) {
            $stats['win_rate'] = round(($stats['matches_won'] / $stats['matches_played']) * 100, 1);
        }

        usort($history, fn($a, $b) => $b['date'] <=> $a['date']);

        return view('teams.show', compact('team', 'history', 'stats'));
    }
}