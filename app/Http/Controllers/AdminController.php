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
}