<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FirebaseService;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    protected $database;

    public function __construct(FirebaseService $firebase)
    {
        $this->database = $firebase->getDatabase();
    }

    /**
     * Show subscription page (available to all).
     */
    public function show()
    {
        $user = Auth::user();
        $subscription = [];
        $transactions = collect();

        if ($user && session('firebase_uid')) {
            $firebaseUid = session('firebase_uid');

            try {
                $subscription = $this->database->getReference("subscriptions/{$firebaseUid}")->getValue() ?? [];
                $transactions = collect($this->database->getReference('transaction_history')->getValue() ?? [])
                    ->filter(fn($t) => ($t['userId'] ?? null) === $firebaseUid)
                    ->values();
            } catch (\Exception $e) {
                report($e);
            }
        }

        return view('pages.subscription', [
            'user' => $user,
            'subscription' => $subscription,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Start a new subscription (requires login).
     */
    public function start(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Please log in to subscribe.');
        }

        $firebaseUid = session('firebase_uid');
        if (!$firebaseUid) {
            return redirect('/login')->with('error', 'Session expired. Please log in again.');
        }

        try {
            $start = Carbon::now();
            $expiry = $start->copy()->addMonth();

            $subscriptionData = [
                'userId' => $firebaseUid,
                'subscriptionType' => 'premium_monthly',
                'paymentMethod' => 'gopay',
                'startDate' => $start->getTimestampMs(),
                'expiryDate' => $expiry->getTimestampMs(),
                'active' => true,
                'transactionId' => uniqid(),
            ];

            $this->database->getReference("subscriptions/{$firebaseUid}")
                ->set($subscriptionData);

            // Record transaction
            $transaction = [
                'userId' => $firebaseUid,
                'item' => 'Premium Monthly Plan',
                'amount' => 49000,
                'paymentMethod' => 'gopay',
                'timestamp' => Carbon::now()->getTimestampMs(),
                'status' => 'success',
                'subscriptionType' => 'premium_monthly'
            ];

            $this->database->getReference('transaction_history')->push($transaction);

            return redirect()->route('subscription.show')->with('success', 'Subscription activated successfully!');
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to start subscription: ' . $e->getMessage());
        }
    }

    /**
     * Cancel subscription (requires login).
     */
    public function cancel(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Please log in first.');
        }

        $firebaseUid = session('firebase_uid');
        if (!$firebaseUid) {
            return redirect('/login')->with('error', 'Session expired. Please log in again.');
        }

        try {
            $subscriptionRef = $this->database->getReference("subscriptions/{$firebaseUid}");
            $subscription = $subscriptionRef->getValue();

            if (!$subscription) {
                return back()->with('error', 'No active subscription found.');
            }

            $subscription['active'] = false;
            $subscription['expiryDate'] = Carbon::now()->getTimestampMs();
            $subscriptionRef->set($subscription);

            $transaction = [
                'userId' => $firebaseUid,
                'item' => 'Subscription Cancelled',
                'amount' => 0,
                'paymentMethod' => $subscription['paymentMethod'] ?? '-',
                'timestamp' => Carbon::now()->getTimestampMs(),
                'status' => 'cancelled',
                'subscriptionType' => $subscription['subscriptionType'] ?? 'premium_monthly'
            ];

            $this->database->getReference('transaction_history')->push($transaction);

            return redirect()->route('subscription.show')->with('success', 'Subscription cancelled successfully.');
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to cancel subscription: ' . $e->getMessage());
        }
    }
}
