<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;

class AdminController extends Controller
{
    protected $database;

    public function __construct(FirebaseService $firebase)
    {
        $this->database = $firebase->getDatabase();
    }

    public function index()
    {
        try {
            // 1. Fetch All Data Nodes
            $users = $this->database->getReference('users')->getValue() ?? [];
            $games = $this->database->getReference('game_sessions')->getValue() ?? [];
            $tournaments = $this->database->getReference('tournaments')->getValue() ?? [];
            $transactions = $this->database->getReference('transaction_history')->getValue() ?? [];

            // 2. Calculate Basic Counts (Cards)
            $usersCount = count($users);
            $gamesCount = count($games);
            $tournamentsCount = count($tournaments);
            
            // 3. Calculate Revenue & Financial Charts
            $totalRevenue = 0;
            $monthlyRevenue = []; // Format: ['Jan' => 1000, 'Feb' => 2000]
            
            foreach ($transactions as $txn) {
                if (isset($txn['amount']) && ($txn['status'] ?? 'success') == 'success') {
                    $amount = (int)$txn['amount'];
                    $totalRevenue += $amount;

                    // Group by Month for Line Chart
                    // Timestamp is in milliseconds, divide by 1000
                    $month = date('M', $txn['timestamp'] / 1000); 
                    if (!isset($monthlyRevenue[$month])) {
                        $monthlyRevenue[$month] = 0;
                    }
                    $monthlyRevenue[$month] += $amount;
                }
            }

            // 4. Calculate User Demographics (Pie Chart)
            $premiumUsers = 0;
            $freeUsers = 0;
            foreach ($users as $user) {
                if (isset($user['isPremium']) && $user['isPremium'] === true) {
                    $premiumUsers++;
                } else {
                    $freeUsers++;
                }
            }

            // Prepare data for View
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

    /**
     * Financial Reports Page
     */
    public function financialReports()
    {
        try {
            // 1. Fetch Transactions
            $transactions = $this->database->getReference('transaction_history')->getValue() ?? [];
            
            // Initialize Data Containers
            $totalRevenue = 0;
            $totalTransactions = count($transactions);
            $successfulTransactions = 0;
            
            $revenueByMonth = []; // For Line Chart
            $paymentMethods = []; // For Pie Chart
            $statuses = [         // For Donut Chart
                'success' => 0, 
                'pending' => 0, 
                'failed' => 0, 
                'cancelled' => 0
            ];

            // 2. Process Data
            foreach ($transactions as $txn) {
                $amount = (int)($txn['amount'] ?? 0);
                $status = $txn['status'] ?? 'unknown';
                $method = $txn['paymentMethod'] ?? 'unknown';
                $timestamp = ($txn['timestamp'] ?? 0) / 1000; // Convert ms to seconds

                // Count Status
                if (isset($statuses[$status])) {
                    $statuses[$status]++;
                } else {
                    $statuses['other'] = ($statuses['other'] ?? 0) + 1;
                }

                // Process Successful Payments
                if ($status == 'success') {
                    $successfulTransactions++;
                    $totalRevenue += $amount;

                    // Group Revenue by Month (e.g., "Nov 2025")
                    $monthKey = date('M Y', $timestamp);
                    if (!isset($revenueByMonth[$monthKey])) {
                        $revenueByMonth[$monthKey] = 0;
                    }
                    $revenueByMonth[$monthKey] += $amount;
                }

                // Count Payment Methods (Regardless of status, to see user preference)
                if (!isset($paymentMethods[$method])) {
                    $paymentMethods[$method] = 0;
                }
                $paymentMethods[$method]++;
            }

            // 3. Sort Data for Charts
            // Sort months chronologically is tricky with strings, but if data is sequential it works.
            // For robustness, you might sort by timestamp key, but array_reverse serves simple lists.
            
            // Sort transactions list by date (newest first) for the table
            $recentTransactions = collect($transactions)->sortByDesc('timestamp');

            $data = [
                'summary' => [
                    'total_revenue' => $totalRevenue,
                    'total_txns' => $totalTransactions,
                    'success_rate' => $totalTransactions > 0 ? round(($successfulTransactions / $totalTransactions) * 100, 1) : 0,
                    'avg_ticket' => $successfulTransactions > 0 ? round($totalRevenue / $successfulTransactions) : 0
                ],
                'charts' => [
                    'revenue' => [
                        'labels' => array_keys($revenueByMonth),
                        'data' => array_values($revenueByMonth)
                    ],
                    'methods' => [
                        'labels' => array_map(fn($m) => ucfirst(str_replace('_', ' ', $m)), array_keys($paymentMethods)),
                        'data' => array_values($paymentMethods)
                    ],
                    'status' => [
                        'labels' => array_map('ucfirst', array_keys($statuses)),
                        'data' => array_values($statuses)
                    ]
                ],
                'table' => $recentTransactions
            ];

            return view('admin.reports.financial', compact('data'));

        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to generate financial report.');
        }
    }

    /**
     * User Reports Page
     */
    public function userReports()
    {
        try {
            // 1. Fetch Users
            $users = $this->database->getReference('users')->getValue() ?? [];
            
            // Initialize Counters
            $totalUsers = count($users);
            $premiumUsers = 0;
            $freeUsers = 0;
            $admins = 0;
            $activeRecently = 0; // Users active in last 30 days

            $tableData = [];

            // 2. Process Data
            foreach ($users as $uid => $user) {
                $isPremium = $user['isPremium'] ?? false;
                $isAdmin = $user['isAdmin'] ?? false;
                $lastUpdated = $user['lastUpdated'] ?? 0;

                // Counters
                if ($isPremium) $premiumUsers++;
                else $freeUsers++;

                if ($isAdmin) $admins++;

                // Check activity (Last 30 days)
                // Timestamp is in ms
                $thirtyDaysAgo = \Carbon\Carbon::now()->subDays(30)->getTimestampMs();
                if ($lastUpdated > $thirtyDaysAgo) {
                    $activeRecently++;
                }

                // Prepare Table Row Data
                $tableData[] = [
                    'uid' => $uid,
                    'name' => $user['displayName'] ?? 'Unknown',
                    'email' => $user['email'] ?? '-',
                    'role' => $isAdmin ? 'Admin' : 'User',
                    'status' => $isPremium ? 'Premium' : 'Free',
                    'last_active' => $lastUpdated
                ];
            }

            $data = [
                'counts' => [
                    'total' => $totalUsers,
                    'premium' => $premiumUsers,
                    'admins' => $admins,
                    'active' => $activeRecently
                ],
                'charts' => [
                    'status' => [
                        'labels' => ['Premium', 'Free'],
                        'data' => [$premiumUsers, $freeUsers]
                    ]
                ],
                'table' => $tableData
            ];

            return view('admin.reports.users', compact('data'));

        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to generate user report.');
        }
    }

    /**
     * Game Session Reports Page
     */
    public function gameReports()
    {
        try {
            // 1. Fetch Games
            $games = $this->database->getReference('game_sessions')->getValue() ?? [];
            
            // Initialize Counters
            $totalGames = count($games);
            $completedGames = 0;
            $activeGames = 0;
            $totalPointsScored = 0;
            $highestScore = 0;

            $tableData = [];

            // 2. Process Data
            foreach ($games as $id => $session) {
                $state = $session['game_state'] ?? [];
                
                $isEnded = $state['isMatchEnded'] ?? false;
                $homeScore = (int)($state['homeScore'] ?? 0);
                $awayScore = (int)($state['awayScore'] ?? 0);
                $totalMatchScore = $homeScore + $awayScore;

                // Counters
                if ($isEnded) {
                    $completedGames++;
                    $totalPointsScored += $totalMatchScore;
                    if ($totalMatchScore > $highestScore) {
                        $highestScore = $totalMatchScore;
                    }
                } else {
                    $activeGames++;
                }

                // Try to find a "date" (using logs if available, otherwise null)
                $lastLogTimestamp = null;
                if (isset($session['game_logs']['match_logs'])) {
                    // Get the last log to approximate game time
                    $lastLog = end($session['game_logs']['match_logs']);
                    $lastLogTimestamp = $lastLog['timestamp'] ?? null;
                }

                // Prepare Table Row
                $tableData[] = [
                    'id' => $id,
                    'name' => $state['sessionName'] ?? 'Untitled Game',
                    'home' => $state['homeTeamName'] ?? 'HOME',
                    'away' => $state['awayTeamName'] ?? 'AWAY',
                    'score' => "{$homeScore} - {$awayScore}",
                    'status' => $isEnded ? 'Completed' : 'In Progress',
                    'timestamp' => $lastLogTimestamp // Can be null
                ];
            }

            // Sort table by latest activity (timestamp)
            usort($tableData, fn($a, $b) => ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0));

            $avgScore = $completedGames > 0 ? round($totalPointsScored / $completedGames) : 0;

            $data = [
                'counts' => [
                    'total' => $totalGames,
                    'completed' => $completedGames,
                    'active' => $activeGames,
                    'avg_score' => $avgScore,
                    'high_score' => $highestScore
                ],
                'charts' => [
                    'status' => [
                        'labels' => ['Completed', 'In Progress'],
                        'data' => [$completedGames, $activeGames]
                    ]
                ],
                'table' => $tableData
            ];

            return view('admin.reports.games', compact('data'));

        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to generate game report.');
        }
    }

    /**
     * Tournament Reports Page
     */
    public function tournamentReports()
    {
        try {
            // 1. Fetch Tournaments
            $tournaments = $this->database->getReference('tournaments')->getValue() ?? [];
            
            // Initialize Counters
            $totalTournaments = count($tournaments);
            $completed = 0;
            $upcoming = 0;
            $totalTeams = 0;
            $mostPopularSize = [4 => 0, 8 => 0, 16 => 0];

            $tableData = [];

            // 2. Process Data
            foreach ($tournaments as $id => $t) {
                $status = $t['status'] ?? 'upcoming';
                $count = (int)($t['participantCount'] ?? 0);
                
                // Update Counters
                if ($status === 'completed') $completed++;
                else $upcoming++; // Counts 'upcoming' and 'ongoing' together for simplicity

                $totalTeams += $count;

                // Track Size Popularity
                if (isset($mostPopularSize[$count])) {
                    $mostPopularSize[$count]++;
                }

                // Prepare Table Data
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

            // Determine most popular format
            $popularFormat = array_keys($mostPopularSize, max($mostPopularSize))[0] ?? 8;

            $data = [
                'counts' => [
                    'total' => $totalTournaments,
                    'completed' => $completed,
                    'active' => $upcoming,
                    'total_participants' => $totalTeams,
                    'popular_format' => $popularFormat
                ],
                'charts' => [
                    'status' => [
                        'labels' => ['Completed', 'Active/Upcoming'],
                        'data' => [$completed, $upcoming]
                    ],
                    'sizes' => [
                        'labels' => ['4 Teams', '8 Teams', '16 Teams'],
                        'data' => array_values($mostPopularSize)
                    ]
                ],
                'table' => $tableData
            ];

            return view('admin.reports.tournaments', compact('data'));

        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to generate tournament report.');
        }
    }

    /**
     * MASTER USERS: List all Firebase Users
     */
    public function users(Request $request)
    {
        try {
            $factory = (new \Kreait\Firebase\Factory)
                ->withServiceAccount(base_path('firebase_credentials.json'));
            $auth = $factory->createAuth();

            // List users (default limit is 1000)
            $users = $auth->listUsers($defaultMaxResults = 1000, $defaultBatchSize = 1000);

            // We also need to fetch the 'users' node from Realtime DB to see who is Premium/Admin
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

    /**
     * MASTER USERS: Toggle Disabled Status
     */
    public function toggleUserStatus($uid)
    {
        try {
            $factory = (new \Kreait\Firebase\Factory)
                ->withServiceAccount(base_path('firebase_credentials.json'));
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

    /**
     * MASTER USERS: Delete User
     */
    public function deleteUser($uid)
    {
        try {
            $factory = (new \Kreait\Firebase\Factory)
                ->withServiceAccount(base_path('firebase_credentials.json'));
            $auth = $factory->createAuth();

            // Delete from Auth
            $auth->deleteUser($uid);

            // Optional: Delete from Realtime Database too
            $this->database->getReference("users/{$uid}")->remove();
            $this->database->getReference("subscriptions/{$uid}")->remove();

            return back()->with('success', 'User deleted permanently.');

        } catch (\Exception $e) {
            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * MASTER USERS: Update User Details
     */
    public function updateUser(Request $request, $uid)
    {
        $request->validate([
            'displayName' => 'required|string|max:255',
            'email' => 'required|email',
            'role' => 'required|in:admin,user',
            'membership' => 'required|in:premium,free',
        ]);

        try {
            $factory = (new \Kreait\Firebase\Factory)
                ->withServiceAccount(base_path('firebase_credentials.json'));
            $auth = $factory->createAuth();

            // 1. Update Firebase Auth (Display Name & Email)
            $properties = [
                'displayName' => $request->displayName,
                'email' => $request->email,
                // 'emailVerified' => true, // Optional: auto-verify email
            ];
            
            // Only update password if provided
            if ($request->filled('password')) {
                $properties['password'] = $request->password;
            }

            $auth->updateUser($uid, $properties);

            // 2. Update Realtime Database (Role & Membership)
            $updates = [
                'displayName' => $request->displayName,
                'email' => $request->email,
                'isAdmin' => ($request->role === 'admin'),
                'isPremium' => ($request->membership === 'premium'),
                'lastUpdated' => now()->getTimestampMs()
            ];

            // If manually setting to Premium, ensure subscription node exists/updates
            if ($request->membership === 'premium') {
                // Optional: You might want to set a default expiry date (e.g., 1 month from now)
                // This prevents the app from immediately reverting them to free if no sub exists
                $expiry = now()->addMonth()->getTimestampMs();
                $this->database->getReference("subscriptions/{$uid}")->update([
                    'active' => true,
                    'expiryDate' => $expiry,
                    'subscriptionType' => 'manual_admin_grant'
                ]);
            } elseif ($request->membership === 'free') {
                // If setting to free, disable subscription
                $this->database->getReference("subscriptions/{$uid}")->update(['active' => false]);
            }

            $this->database->getReference("users/{$uid}")->update($updates);

            return back()->with('success', 'User updated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }
}