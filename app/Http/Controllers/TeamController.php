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

        $teamName = $team['name']; // Vital for matching

        // 2. Find Tournaments & Specific Matches
        $allTournaments = $this->database->getReference('tournaments')->getValue();
        $history = [];
        $stats = ['played' => 0, 'won' => 0];

        if ($allTournaments) {
            foreach ($allTournaments as $tId => $tData) {
                
                // A. Check if team participated (by Name or ID link)
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

                // B. Find specific matches played by this team
                $matchesPlayed = [];
                if (isset($tData['matches'])) {
                    foreach ($tData['matches'] as $mId => $match) {
                        $home = $match['home'] ?? '';
                        $away = $match['away'] ?? '';

                        // If team played in this match
                        if ($home === $teamName || $away === $teamName) {
                            $isHome = ($home === $teamName);
                            $opponent = $isHome ? $away : $home;
                            
                            // Determine Result
                            $result = 'Scheduled';
                            $score = 'vs';
                            $resultColor = 'secondary';

                            if (($match['status'] ?? '') === 'completed') {
                                $myScore = $isHome ? ($match['scoreHome'] ?? 0) : ($match['scoreAway'] ?? 0);
                                $oppScore = $isHome ? ($match['scoreAway'] ?? 0) : ($match['scoreHome'] ?? 0);
                                
                                $score = "{$myScore} - {$oppScore}";
                                
                                if (($match['winner'] ?? '') === $teamName) {
                                    $result = 'WON';
                                    $resultColor = 'success';
                                } else {
                                    $result = 'LOST';
                                    $resultColor = 'danger';
                                }
                            }

                            $matchesPlayed[] = [
                                'matchId' => $mId, // Key link to Game Overview!
                                'round' => $match['round'] ?? 1,
                                'opponent' => $opponent ?: 'TBD',
                                'result' => $result,
                                'resultColor' => $resultColor,
                                'score' => $score,
                                'status' => $match['status'] ?? 'scheduled'
                            ];
                        }
                    }
                }

                // Sort matches by round
                usort($matchesPlayed, fn($a, $b) => $a['round'] <=> $b['round']);

                // C. Determine Overall Tournament Result
                $overallResult = 'Participant';
                if (($tData['status'] ?? '') === 'completed') {
                    if (($tData['winner'] ?? '') === $teamName) {
                        $overallResult = 'Champion';
                        $stats['won']++;
                    } else {
                        $overallResult = 'Eliminated';
                    }
                }

                $history[] = [
                    'id' => $tId,
                    'tournamentName' => $tData['name'],
                    'date' => $tData['startDate'],
                    'result' => $overallResult,
                    'matches' => $matchesPlayed // Pass the match list to view
                ];
                $stats['played']++;
            }
        }

        // Sort history by date descending
        usort($history, fn($a, $b) => $b['date'] <=> $a['date']);

        return view('teams.show', compact('team', 'history', 'stats'));
    }
}