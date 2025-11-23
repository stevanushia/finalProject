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
}