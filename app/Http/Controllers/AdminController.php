<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FirebaseService;
use Barryvdh\DomPDF\Facade\Pdf; // <--- IMPORTANT: PDF Import

class AdminController extends Controller
{
    protected $database;

    public function __construct(FirebaseService $firebase)
    {
        $this->database = $firebase->getDatabase();
    }

    // =========================================================================
    // 1. DASHBOARD OVERVIEW
    // =========================================================================

    public function index()
    {
        try {
            // 1. Fetch All Data Nodes
            $users = $this->database->getReference('users')->getValue() ?? [];
            $games = $this->database->getReference('game_sessions')->getValue() ?? [];
            $tournaments = $this->database->getReference('tournaments')->getValue() ?? [];
            $transactions = $this->database->getReference('transaction_history')->getValue() ?? [];

            // 2. Calculate Basic Counts
            $usersCount = count($users);
            $gamesCount = count($games);
            $tournamentsCount = count($tournaments);
            
            // 3. Calculate Revenue & Financial Charts
            $totalRevenue = 0;
            $monthlyRevenue = []; 
            
            foreach ($transactions as $txn) {
                if (isset($txn['amount']) && ($txn['status'] ?? 'success') == 'success') {
                    $amount = (int)$txn['amount'];
                    $totalRevenue += $amount;

                    // Group by Month
                    $month = date('M', $txn['timestamp'] / 1000); 
                    if (!isset($monthlyRevenue[$month])) {
                        $monthlyRevenue[$month] = 0;
                    }
                    $monthlyRevenue[$month] += $amount;
                }
            }

            // 4. Calculate User Demographics
            $premiumUsers = 0;
            $freeUsers = 0;
            foreach ($users as $user) {
                if (isset($user['isPremium']) && $user['isPremium'] === true) {
                    $premiumUsers++;
                } else {
                    $freeUsers++;
                }
            }

            $stats = [
                'counts' => [
                    'users' => $usersCount,
                    'games' => $gamesCount,
                    'tournaments' => $tournamentsCount,
                    'revenue' => $totalRevenue
                ],
                'charts' => [
                    'revenue' => [
                        'labels' => array_keys($monthlyRevenue),
                        'data' => array_values($monthlyRevenue)
                    ],
                    'users' => [
                        'premium' => $premiumUsers,
                        'free' => $freeUsers
                    ]
                ]
            ];

            return view('admin.dashboard', compact('stats'));

        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to load dashboard data.');
        }
    }

    // =========================================================================
    // 2. REPORTS (VIEW & EXPORT)
    // =========================================================================

    /**
     * Financial Reports View
     */
    public function financialReports()
    {
        try {
            $data = $this->getFinancialData();
            return view('admin.reports.financial', compact('data'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load financial report.');
        }
    }

    /**
     * Financial Reports PDF
     */
    public function exportFinancialReport()
    {
        try {
            $data = $this->getFinancialData();
            $pdf = Pdf::loadView('admin.exports.financial', compact('data'));
            return $pdf->download('Financial_Report_' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export PDF.');
        }
    }

    /**
     * User Reports View
     */
    public function userReports()
    {
        try {
            $data = $this->getUserData();
            return view('admin.reports.users', compact('data'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load user report.');
        }
    }

    /**
     * User Reports PDF
     */
    public function exportUserReport()
    {
        try {
            $data = $this->getUserData();
            $pdf = Pdf::loadView('admin.exports.users', compact('data'));
            return $pdf->download('User_Report_' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export PDF.');
        }
    }

    /**
     * Game Reports View
     */
    public function gameReports()
    {
        try {
            $data = $this->getGameData();
            return view('admin.reports.games', compact('data'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load game report.');
        }
    }

    /**
     * Game Reports PDF
     */
    public function exportGameReport()
    {
        try {
            $data = $this->getGameData();
            $pdf = Pdf::loadView('admin.exports.games', compact('data'));
            return $pdf->download('Game_Report_' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export PDF.');
        }
    }

    /**
     * Tournament Reports View
     */
    public function tournamentReports()
    {
        try {
            $data = $this->getTournamentData();
            return view('admin.reports.tournaments', compact('data'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load tournament report.');
        }
    }

    /**
     * Tournament Reports PDF
     */
    public function exportTournamentReport()
    {
        try {
            $data = $this->getTournamentData();
            $pdf = Pdf::loadView('admin.exports.tournaments', compact('data'));
            return $pdf->download('Tournament_Report_' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export PDF.');
        }
    }

    // =========================================================================
    // 3. PRIVATE DATA HELPER METHODS (The Logic Engine)
    // =========================================================================

    private function getFinancialData()
    {
        $transactions = $this->database->getReference('transaction_history')->getValue() ?? [];
        
        $totalRevenue = 0;
        $totalTransactions = count($transactions);
        $successfulTransactions = 0;
        
        $revenueByMonth = [];
        $paymentMethods = [];
        $statuses = ['success' => 0, 'pending' => 0, 'failed' => 0, 'cancelled' => 0];

        foreach ($transactions as $txn) {
            $amount = (int)($txn['amount'] ?? 0);
            $status = $txn['status'] ?? 'unknown';
            $method = $txn['paymentMethod'] ?? 'unknown';
            $timestamp = ($txn['timestamp'] ?? 0) / 1000;

            if (isset($statuses[$status])) $statuses[$status]++;
            else $statuses['other'] = ($statuses['other'] ?? 0) + 1;

            if ($status == 'success') {
                $successfulTransactions++;
                $totalRevenue += $amount;
                $monthKey = date('M Y', $timestamp);
                if (!isset($revenueByMonth[$monthKey])) $revenueByMonth[$monthKey] = 0;
                $revenueByMonth[$monthKey] += $amount;
            }

            if (!isset($paymentMethods[$method])) $paymentMethods[$method] = 0;
            $paymentMethods[$method]++;
        }

        $recentTransactions = collect($transactions)->sortByDesc('timestamp');

        return [
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_txns' => $totalTransactions,
                'success_rate' => $totalTransactions > 0 ? round(($successfulTransactions / $totalTransactions) * 100, 1) : 0,
                'avg_ticket' => $successfulTransactions > 0 ? round($totalRevenue / $successfulTransactions) : 0
            ],
            'charts' => [
                'revenue' => ['labels' => array_keys($revenueByMonth), 'data' => array_values($revenueByMonth)],
                'methods' => ['labels' => array_map(fn($m) => ucfirst(str_replace('_', ' ', $m)), array_keys($paymentMethods)), 'data' => array_values($paymentMethods)],
                'status' => ['labels' => array_map('ucfirst', array_keys($statuses)), 'data' => array_values($statuses)]
            ],
            'table' => $recentTransactions
        ];
    }

    private function getUserData()
    {
        $users = $this->database->getReference('users')->getValue() ?? [];
        
        $totalUsers = count($users);
        $premiumUsers = 0;
        $freeUsers = 0;
        $admins = 0;
        $activeRecently = 0;
        $tableData = [];

        foreach ($users as $uid => $user) {
            $isPremium = $user['isPremium'] ?? false;
            $isAdmin = $user['isAdmin'] ?? false;
            $lastUpdated = $user['lastUpdated'] ?? 0;

            if ($isPremium) $premiumUsers++; else $freeUsers++;
            if ($isAdmin) $admins++;

            $thirtyDaysAgo = \Carbon\Carbon::now()->subDays(30)->getTimestampMs();
            if ($lastUpdated > $thirtyDaysAgo) $activeRecently++;

            $tableData[] = [
                'uid' => $uid,
                'name' => $user['displayName'] ?? 'Unknown',
                'email' => $user['email'] ?? '-',
                'role' => $isAdmin ? 'Admin' : 'User',
                'status' => $isPremium ? 'Premium' : 'Free',
                'last_active' => $lastUpdated
            ];
        }

        return [
            'counts' => ['total' => $totalUsers, 'premium' => $premiumUsers, 'admins' => $admins, 'active' => $activeRecently],
            'charts' => ['status' => ['labels' => ['Premium', 'Free'], 'data' => [$premiumUsers, $freeUsers]]],
            'table' => $tableData
        ];
    }

    private function getGameData()
    {
        $games = $this->database->getReference('game_sessions')->getValue() ?? [];
        
        $totalGames = count($games);
        $completedGames = 0;
        $activeGames = 0;
        $totalPointsScored = 0;
        $highestScore = 0;
        $tableData = [];

        foreach ($games as $id => $session) {
            $state = $session['game_state'] ?? [];
            $isEnded = $state['isMatchEnded'] ?? false;
            $homeScore = (int)($state['homeScore'] ?? 0);
            $awayScore = (int)($state['awayScore'] ?? 0);
            $totalMatchScore = $homeScore + $awayScore;

            if ($isEnded) {
                $completedGames++;
                $totalPointsScored += $totalMatchScore;
                if ($totalMatchScore > $highestScore) $highestScore = $totalMatchScore;
            } else {
                $activeGames++;
            }

            $lastLogTimestamp = null;
            if (isset($session['game_logs']['match_logs']) && is_array($session['game_logs']['match_logs'])) {
                $lastLog = end($session['game_logs']['match_logs']);
                $lastLogTimestamp = $lastLog['timestamp'] ?? null;
            }

            $tableData[] = [
                'id' => $id,
                'name' => $state['sessionName'] ?? 'Untitled Game',
                'home' => $state['homeTeamName'] ?? 'HOME',
                'away' => $state['awayTeamName'] ?? 'AWAY',
                'score' => "{$homeScore} - {$awayScore}",
                'status' => $isEnded ? 'Completed' : 'In Progress',
                'timestamp' => $lastLogTimestamp
            ];
        }

        usort($tableData, fn($a, $b) => ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0));
        $avgScore = $completedGames > 0 ? round($totalPointsScored / $completedGames) : 0;

        return [
            'counts' => ['total' => $totalGames, 'completed' => $completedGames, 'active' => $activeGames, 'avg_score' => $avgScore, 'high_score' => $highestScore],
            'charts' => ['status' => ['labels' => ['Completed', 'In Progress'], 'data' => [$completedGames, $activeGames]]],
            'table' => $tableData
        ];
    }

    private function getTournamentData()
    {
        $tournaments = $this->database->getReference('tournaments')->getValue() ?? [];
        
        $totalTournaments = count($tournaments);
        $completed = 0;
        $upcoming = 0;
        $totalTeams = 0;
        $mostPopularSize = [4 => 0, 8 => 0, 16 => 0];
        $tableData = [];

        foreach ($tournaments as $id => $t) {
            $status = $t['status'] ?? 'upcoming';
            $count = (int)($t['participantCount'] ?? 0);
            
            if ($status === 'completed') $completed++; else $upcoming++;
            $totalTeams += $count;
            if (isset($mostPopularSize[$count])) $mostPopularSize[$count]++;

            $tableData[] = [
                'id' => $id,
                'name' => $t['name'] ?? 'Untitled',
                'teams' => $count,
                'status' => $status,
                'winner' => $t['winner'] ?? '-',
                'start_date' => $t['startDate'] ?? null,
                'created_at' => $t['createdAt'] ?? null
            ];
        }

        $popularFormat = array_keys($mostPopularSize, max($mostPopularSize))[0] ?? 8;

        return [
            'counts' => ['total' => $totalTournaments, 'completed' => $completed, 'active' => $upcoming, 'total_participants' => $totalTeams, 'popular_format' => $popularFormat],
            'charts' => [
                'status' => ['labels' => ['Completed', 'Active/Upcoming'], 'data' => [$completed, $upcoming]],
                'sizes' => ['labels' => ['4 Teams', '8 Teams', '16 Teams'], 'data' => array_values($mostPopularSize)]
            ],
            'table' => $tableData
        ];
    }

    // =========================================================================
    // 4. MASTER MANAGEMENT (Users, Tournaments, etc.)
    // =========================================================================

    public function users(Request $request)
    {
        try {
            $factory = (new \Kreait\Firebase\Factory)->withServiceAccount(base_path('firebase_credentials.json'));
            $auth = $factory->createAuth();
            $users = $auth->listUsers(1000, 1000);
            $dbUsers = $this->database->getReference('users')->getValue() ?? [];

            $userList = [];
            foreach ($users as $user) {
                $uid = $user->uid;
                $dbData = $dbUsers[$uid] ?? [];
                $userList[] = [
                    'uid' => $uid,
                    'email' => $user->email,
                    'displayName' => $user->displayName,
                    'photoUrl' => $user->photoUrl,
                    'disabled' => $user->disabled,
                    'lastLogin' => $user->metadata->lastLoginAt ? $user->metadata->lastLoginAt->format('Y-m-d H:i') : 'Never',
                    'createdAt' => $user->metadata->createdAt ? $user->metadata->createdAt->format('Y-m-d H:i') : '-',
                    'isPremium' => $dbData['isPremium'] ?? false,
                    'isAdmin' => $dbData['isAdmin'] ?? false,
                ];
            }
            return view('admin.users.index', compact('userList'));
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to fetch users: ' . $e->getMessage());
        }
    }

    public function updateUser(Request $request, $uid)
    {
        $request->validate([
            'displayName' => 'required|string|max:255',
            'email' => 'required|email',
            'role' => 'required|in:admin,user',
            'membership' => 'required|in:premium,free',
        ]);

        try {
            $factory = (new \Kreait\Firebase\Factory)->withServiceAccount(base_path('firebase_credentials.json'));
            $auth = $factory->createAuth();

            $properties = ['displayName' => $request->displayName, 'email' => $request->email];
            if ($request->filled('password')) {
                $properties['password'] = $request->password;
            }
            $auth->updateUser($uid, $properties);

            $updates = [
                'displayName' => $request->displayName,
                'email' => $request->email,
                'isAdmin' => ($request->role === 'admin'),
                'isPremium' => ($request->membership === 'premium'),
                'lastUpdated' => now()->getTimestampMs()
            ];

            if ($request->membership === 'premium') {
                $expiry = now()->addMonth()->getTimestampMs();
                $this->database->getReference("subscriptions/{$uid}")->update([
                    'active' => true, 'expiryDate' => $expiry, 'subscriptionType' => 'manual_admin_grant'
                ]);
            } elseif ($request->membership === 'free') {
                $this->database->getReference("subscriptions/{$uid}")->update(['active' => false]);
            }

            $this->database->getReference("users/{$uid}")->update($updates);
            return back()->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function toggleUserStatus($uid)
    {
        try {
            $factory = (new \Kreait\Firebase\Factory)->withServiceAccount(base_path('firebase_credentials.json'));
            $auth = $factory->createAuth();
            $user = $auth->getUser($uid);
            
            if ($user->disabled) {
                $auth->enableUser($uid);
                $msg = 'User enabled successfully.';
            } else {
                $auth->disableUser($uid);
                $msg = 'User disabled successfully.';
            }
            return back()->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', 'Action failed: ' . $e->getMessage());
        }
    }

    public function deleteUser($uid)
    {
        try {
            $factory = (new \Kreait\Firebase\Factory)->withServiceAccount(base_path('firebase_credentials.json'));
            $auth = $factory->createAuth();
            $auth->deleteUser($uid);
            $this->database->getReference("users/{$uid}")->remove();
            $this->database->getReference("subscriptions/{$uid}")->remove();
            return back()->with('success', 'User deleted permanently.');
        } catch (\Exception $e) {
            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    public function masterTournaments()
    {
        try {
            $tournaments = $this->database->getReference('tournaments')->getValue() ?? [];
            return view('admin.tournaments.index', compact('tournaments'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load tournaments.');
        }
    }

    public function editTournament($id)
    {
        try {
            $tournament = $this->database->getReference("tournaments/{$id}")->getValue();
            if (!$tournament) return back()->with('error', 'Tournament not found');
            return view('admin.tournaments.edit', compact('tournament', 'id'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function updateTournament(Request $request, $id)
    {
        $request->validate(['name' => 'required|string', 'start_date' => 'required|date', 'status' => 'required|in:upcoming,ongoing,completed']);
        try {
            $timestamp = \Carbon\Carbon::parse($request->start_date)->getTimestampMs();
            $this->database->getReference("tournaments/{$id}")->update(['name' => $request->name, 'startDate' => $timestamp, 'status' => $request->status]);
            return back()->with('success', 'Tournament details updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function updateTournamentTeam(Request $request, $id)
    {
        $request->validate(['team_key' => 'required', 'new_name' => 'required|string', 'players' => 'nullable|string']);
        try {
            $oldName = $request->team_key;
            $newName = $request->new_name;
            $players = json_decode($request->players, true) ?? [];
            $ref = $this->database->getReference("tournaments/{$id}/teams");
            
            if ($oldName !== $newName) {
                $ref->getChild($oldName)->remove();
                $ref->getChild($newName)->set(['name' => $newName, 'players' => $players]);
            } else {
                $ref->getChild($oldName)->update(['players' => $players]);
            }
            return back()->with('success', 'Team roster updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Roster update failed: ' . $e->getMessage());
        }
    }

    public function deleteTournament($id)
    {
        $this->database->getReference("tournaments/{$id}")->remove();
        return redirect()->route('admin.tournaments.index')->with('success', 'Tournament deleted.');
    }
    
    // MASTER ANNOUNCEMENTS (From previous step)
    public function announcements()
    {
        try {
            $announcements = $this->database->getReference('announcements')->getValue() ?? [];
            krsort($announcements);
            return view('admin.announcements.index', compact('announcements'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load announcements.');
        }
    }

    public function storeAnnouncement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:50',
            'message' => 'required|string|max:200',
            'type' => 'required|in:info,warning,danger,success',
        ]);

        try {
            $id = $request->id ?? uniqid('ann_');
            $data = [
                'id' => $id,
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'isActive' => $request->has('isActive'),
                'createdAt' => now()->getTimestampMs()
            ];
            $this->database->getReference("announcements/{$id}")->set($data);
            return back()->with('success', 'Announcement saved.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function deleteAnnouncement($id)
    {
        $this->database->getReference("announcements/{$id}")->remove();
        return back()->with('success', 'Announcement deleted.');
    }
}