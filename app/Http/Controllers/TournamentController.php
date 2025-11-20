<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FirebaseService;
use Carbon\Carbon;

class TournamentController extends Controller
{
    protected $database;

    public function __construct(FirebaseService $firebase)
    {
        $this->database = $firebase->getDatabase();
    }

    /**
     * List all tournaments
     */
    public function index()
    {
        try {
            $tournaments = $this->database->getReference('tournaments')->getValue();
            // Sort by startDate descending (newest first)
            $tournaments = collect($tournaments ?? [])->sortByDesc('startDate');
            return view('tournaments.index', compact('tournaments'));
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to load tournaments.');
        }
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('tournaments.create');
    }

    /**
     * Store new tournament and generate bracket
     */
    public function store(Request $request)
    {
        // 1. VALIDATION (This checks the input, but doesn't set the variable)
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date|after_or_equal:today', // <--- The rule goes HERE
            'participant_count' => 'required|in:4,8,16',
            'teams' => 'required|array|min:4',
            'teams.*' => 'required|string|distinct'
        ]);

        // ... (team count check) ...

        $firebaseUid = session('firebase_uid');
        if (!$firebaseUid) return redirect('/login')->with('error', 'Session expired.');

        $tournamentId = uniqid('trn_');
        $teams = array_values($request->teams); 
        $matches = $this->generateBracket($teams, $request->participant_count);

        // 2. CONVERT DATE (Calculate the timestamp)
        $startDateTimestamp = \Carbon\Carbon::parse($request->start_date)->getTimestampMs();

        // 3. SAVE DATA
        $tournamentData = [
            'id' => $tournamentId,
            'name' => $request->name,
            'creatorUid' => $firebaseUid,
            'status' => 'upcoming',
            'type' => 'single_elimination',
            'participantCount' => (int)$request->participant_count,
            
            // This is when the tournament BEGINS (User selected)
            'startDate' => $startDateTimestamp, 
            
            // ✅ NEW: This is when you clicked "Generate Bracket" (Now)
            'createdAt' => \Carbon\Carbon::now()->getTimestampMs(), 
            
            'teams' => $teams,
            'matches' => $matches,
            'winner' => null
        ];

        try {
            $this->database->getReference("tournaments/{$tournamentId}")->set($tournamentData);
            return redirect()->route('tournaments.show', $tournamentId)->with('success', 'Tournament created!');
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to create tournament.');
        }
    }

    /**
     * Show tournament bracket
     */
    public function show($id)
    {
        try {
            $tournament = $this->database->getReference("tournaments/{$id}")->getValue();
            
            if (!$tournament) {
                return redirect()->route('tournaments.index')->with('error', 'Tournament not found.');
            }

            // Organize matches by round for easy display
            $rounds = collect($tournament['matches'])->groupBy('round')->sortKeys();

            return view('tournaments.show', compact('tournament', 'rounds'));
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('tournaments.index')->with('error', 'Error loading tournament.');
        }
    }

    /**
     * DELETE a tournament
     */
    public function destroy($id)
    {
        $firebaseUid = session('firebase_uid');
        $user = Auth::user();

        try {
            $ref = $this->database->getReference("tournaments/{$id}");
            $tournament = $ref->getValue();

            if (!$tournament) {
                return back()->with('error', 'Tournament not found.');
            }

            // Security Check: Only Creator or Admin can delete
            $isCreator = ($tournament['creatorUid'] ?? '') === $firebaseUid;
            $isAdmin = $user->isAdmin ?? false;

            if (!$isCreator && !$isAdmin) {
                return back()->with('error', 'Unauthorized action.');
            }

            // Delete from Firebase
            $ref->remove();

            return redirect()->route('tournaments.index')->with('success', 'Tournament deleted successfully.');

        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to delete tournament.');
        }
    }

    /**
     * UPDATED: Update match result AND Team Names
     */
    public function updateMatch(Request $request, $tournamentId, $matchId)
    {
        $request->validate([
            'winner' => 'required|string',
            'score_home' => 'nullable|integer',
            'score_away' => 'nullable|integer',
            'home_team_name' => 'required|string', // New validation
            'away_team_name' => 'required|string', // New validation
        ]);

        try {
            $tournamentRef = $this->database->getReference("tournaments/{$tournamentId}");
            $matches = $tournamentRef->getChild('matches')->getValue();

            if (!isset($matches[$matchId])) {
                return back()->with('error', 'Match not found.');
            }

            $match = $matches[$matchId];
            
            // Determine the winning name based on the NEW input names
            $winningTeamName = $request->winner === 'home' ? $request->home_team_name : $request->away_team_name;
            
            $updates = [
                // Update scores and winner
                "matches/{$matchId}/winner" => $winningTeamName,
                "matches/{$matchId}/scoreHome" => $request->score_home ?? 0,
                "matches/{$matchId}/scoreAway" => $request->score_away ?? 0,
                "matches/{$matchId}/status" => 'completed',
                
                // UPDATE TEAM NAMES (In case user renamed them)
                "matches/{$matchId}/home" => $request->home_team_name,
                "matches/{$matchId}/away" => $request->away_team_name,
            ];

            // Advance winner to next match
            if (isset($match['nextMatchId'])) {
                $nextMatchId = $match['nextMatchId'];
                $slot = $match['nextMatchSlot']; 
                $updates["matches/{$nextMatchId}/{$slot}"] = $winningTeamName;
            } else {
                // Final Match
                $updates["status"] = 'completed';
                $updates["winner"] = $winningTeamName;
            }

            $tournamentRef->update($updates);
            return back()->with('success', 'Match updated successfully.');

        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to update match.');
        }
    }

    /**
     * Helper: Generate Single Elimination Bracket
     */
    private function generateBracket($teams, $count)
    {
        $matches = [];
        $totalRounds = log($count, 2);
        
        // Round 1 (Quarter/Eighth Finals)
        $matchIndex = 1;
        for ($i = 0; $i < $count; $i += 2) {
            $matchId = "r1_m" . ceil(($i + 1) / 2);
            $nextMatchId = "r2_m" . ceil(ceil(($i + 1) / 2) / 2);
            $nextMatchSlot = (ceil(($i + 1) / 2) % 2 != 0) ? 'home' : 'away';

            $matches[$matchId] = [
                'id' => $matchId,
                'round' => 1,
                'home' => $teams[$i],
                'away' => $teams[$i+1],
                'scoreHome' => 0,
                'scoreAway' => 0,
                'winner' => null,
                'status' => 'scheduled',
                'nextMatchId' => ($totalRounds > 1) ? $nextMatchId : null,
                'nextMatchSlot' => ($totalRounds > 1) ? $nextMatchSlot : null
            ];
        }

        // Subsequent Rounds (Empty slots)
        $matchesPerRound = $count / 2;
        for ($r = 2; $r <= $totalRounds; $r++) {
            $matchesPerRound /= 2;
            for ($m = 1; $m <= $matchesPerRound; $m++) {
                $matchId = "r{$r}_m{$m}";
                
                // Calculate next match for this one
                $nextMatchId = null;
                $nextMatchSlot = null;
                if ($r < $totalRounds) {
                    $nextMatchId = "r" . ($r + 1) . "_m" . ceil($m / 2);
                    $nextMatchSlot = ($m % 2 != 0) ? 'home' : 'away';
                }

                $matches[$matchId] = [
                    'id' => $matchId,
                    'round' => $r,
                    'home' => null, // TBD
                    'away' => null, // TBD
                    'scoreHome' => 0,
                    'scoreAway' => 0,
                    'winner' => null,
                    'status' => 'scheduled',
                    'nextMatchId' => $nextMatchId,
                    'nextMatchSlot' => $nextMatchSlot
                ];
            }
        }

        return $matches;
    }
}