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

        // 2. Find Tournaments this team participated in
        $allTournaments = $this->database->getReference('tournaments')->getValue();
        $history = [];
        $stats = ['played' => 0, 'won' => 0];

        if ($allTournaments) {
            foreach ($allTournaments as $tId => $tData) {
                // Check if this team is in the tournament's team list
                // We check by ID first, or fallback to Name match
                $inTournament = false;
                
                if (isset($tData['teams'])) {
                    foreach ($tData['teams'] as $tTeam) {
                        if ((isset($tTeam['teamId']) && $tTeam['teamId'] === $teamId) || 
                            ($tTeam['name'] === $team['name'])) {
                            $inTournament = true;
                            break;
                        }
                    }
                }

                if ($inTournament) {
                    $result = 'Participant';
                    if (($tData['status'] ?? '') === 'completed') {
                        if (($tData['winner'] ?? '') === $team['name']) {
                            $result = 'Champion';
                            $stats['won']++;
                        } else {
                            $result = 'Eliminated';
                        }
                    }

                    $history[] = [
                        'tournamentName' => $tData['name'],
                        'date' => $tData['startDate'],
                        'result' => $result,
                        'id' => $tId
                    ];
                    $stats['played']++;
                }
            }
        }

        // Sort history by date descending
        usort($history, fn($a, $b) => $b['date'] <=> $a['date']);

        return view('teams.show', compact('team', 'history', 'stats'));
    }
}