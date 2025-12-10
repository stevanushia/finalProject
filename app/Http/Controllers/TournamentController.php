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
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'participant_count' => 'required|in:4,8,16',
            'teams' => 'required|array',
            'teams.*.name' => 'required|string|distinct',
            'teams.*.players' => 'nullable|string'
        ]);

        $firebaseUid = session('firebase_uid');
        if (!$firebaseUid) return redirect('/login')->with('error', 'Session expired.');

        $tournamentId = uniqid('trn_');
        $participantCount = (int)$request->participant_count;
        
        // 1. Process Teams
        $teamsData = [];
        $teamNames = [];

        foreach ($request->teams as $index => $data) {
            $teamName = $data['name'];
            $teamNames[] = $teamName;
            
            $players = json_decode($data['players'] ?? '[]', true);
            $teamsData[$teamName] = [
                'name' => $teamName,
                'players' => $players
            ];
        }

        // 2. Generate Bracket with Unique IDs
        // FIX: Pass $tournamentId to ensure match IDs are unique (e.g., trn_123_r1_m1)
        $matches = $this->generateBracket($teamNames, $participantCount, $tournamentId);

        $startDateTimestamp = \Carbon\Carbon::parse($request->start_date)->getTimestampMs();

        $tournamentData = [
            'id' => $tournamentId,
            'name' => $request->name,
            'creatorUid' => $firebaseUid,
            'status' => 'upcoming',
            'type' => 'single_elimination',
            'participantCount' => $participantCount,
            'startDate' => $startDateTimestamp, 
            'createdAt' => \Carbon\Carbon::now()->getTimestampMs(), 
            'teams' => $teamsData,
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
    private function generateBracket($teams, $count, $tournamentId)
    {
        $matches = [];
        $totalRounds = log($count, 2);
        
        // Round 1 (Quarter/Eighth Finals)
        for ($i = 0; $i < $count; $i += 2) {
            $matchNum = ceil(($i + 1) / 2);
            $nextMatchNum = ceil($matchNum / 2);

            // FIX: Prepend Tournament ID to make match ID unique globally
            $matchId = "{$tournamentId}_r1_m{$matchNum}";
            $nextMatchId = "{$tournamentId}_r2_m{$nextMatchNum}";
            
            $nextMatchSlot = ($matchNum % 2 != 0) ? 'home' : 'away';

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
                // FIX: Prepend Tournament ID
                $matchId = "{$tournamentId}_r{$r}_m{$m}";
                
                // Calculate next match
                $nextMatchId = null;
                $nextMatchSlot = null;
                if ($r < $totalRounds) {
                    $nextMatchId = "{$tournamentId}_r" . ($r + 1) . "_m" . ceil($m / 2);
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

    /**
     * Update Team Name and Roster
     */
    public function updateTeam(Request $request, $tournamentId)
    {
        $request->validate([
            'team_key' => 'required', // The original team name (used as key)
            'new_name' => 'required|string',
            'players' => 'nullable|string' // JSON string of players
        ]);

        try {
            // 1. Security Check
            $ref = $this->database->getReference("tournaments/{$tournamentId}");
            $tournament = $ref->getValue();
            
            if (!$tournament) return back()->with('error', 'Tournament not found.');

            $currentUserUid = session('firebase_uid');
            $isCreator = ($tournament['creatorUid'] ?? '') === $currentUserUid;
            $isAdmin = Auth::check() && (Auth::user()->isAdmin ?? false);

            if (!$isCreator && !$isAdmin) {
                return back()->with('error', 'Unauthorized action.');
            }

            // 2. Process Update
            $oldName = $request->team_key;
            $newName = $request->new_name;
            $players = json_decode($request->players, true) ?? [];

            $teamsRef = $this->database->getReference("tournaments/{$tournamentId}/teams");

            if ($oldName !== $newName) {
                // If name changed: Create new key, Delete old key
                $teamsRef->getChild($oldName)->remove();
                $teamsRef->getChild($newName)->set([
                    'name' => $newName,
                    'players' => $players
                ]);
                
                // NOTE: This does NOT automatically rename the team in existing Match History brackets.
                // That would require a complex loop through all matches. 
                // For now, this updates the Roster definition.
            } else {
                // Name is same, just update players
                $teamsRef->getChild($oldName)->update(['players' => $players]);
            }

            return back()->with('success', 'Team roster updated successfully.');

        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to update team: ' . $e->getMessage());
        }
    }
}