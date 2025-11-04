<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FirebaseService;

class ProfileController extends Controller
{
    protected $auth;
    protected $database;

    public function __construct(FirebaseService $firebase)
    {
        $this->auth = $firebase->getAuth();
        $this->database = $firebase->getDatabase();
    }

    public function show(Request $request)
    {

        // Ensure the user is logged in
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Please log in first.');
        }

        $user = Auth::user();
        $firebaseUid = session('firebase_uid');

        if (!$firebaseUid) {
            return redirect('/login')->with('error', 'Firebase UID missing. Please log in again.');
        }

        try {
            // Get data from Firebase Realtime Database
            $userData = $this->database->getReference("users/{$firebaseUid}")->getValue();
            $subscription = $this->database->getReference("subscriptions/{$firebaseUid}")->getValue();

            // Filter transaction history for this user
            $transactions = collect($this->database->getReference('transaction_history')->getValue() ?? [])
                ->filter(fn($t) => $t['userId'] === $firebaseUid)
                ->values();

            // Get Firebase Auth user info (displayName, emailVerified, etc.)
            $firebaseUser = $this->auth->getUser($firebaseUid);

            return view('pages.profile', [
                'user' => $user,                   // Laravel user
                'firebaseUser' => $firebaseUser,   // Firebase Auth user
                'userData' => $userData,           // Extra Firebase data
                'subscription' => $subscription,   // Subscription info
                'transactions' => $transactions,   // Transaction list
            ]);

        } catch (\Exception $e) {
            report($e);
            return redirect('/login')->with('error', 'Unable to load profile: ' . $e->getMessage());
        }
    }
}
