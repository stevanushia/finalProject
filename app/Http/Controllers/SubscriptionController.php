<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Midtrans\Config;
use Midtrans\Snap;

class SubscriptionController extends Controller
{
    protected $database;

    public function __construct(FirebaseService $firebase)
    {
        $this->database = $firebase->getDatabase();
        
        // Configure Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
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
                    ->sortByDesc('timestamp')
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
     * Create Midtrans payment token
     */
    public function createPayment(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Please log in to subscribe.'], 401);
        }

        $firebaseUid = session('firebase_uid');
        $user = Auth::user();

        if (!$firebaseUid) {
            return response()->json(['error' => 'Session expired.'], 401);
        }

        try {
            $orderId = 'SUB-' . $firebaseUid . '-' . time();
            
            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => 49000,
                ],
                'customer_details' => [
                    'first_name' => $user->name ?? 'Customer',
                    'email' => $user->email ?? 'customer@example.com',
                ],
                'item_details' => [
                    [
                        'id' => 'premium_monthly',
                        'price' => 49000,
                        'quantity' => 1,
                        'name' => 'Premium Monthly Subscription'
                    ]
                ],
                'enabled_payments' => ['gopay', 'shopeepay', 'other_qris', 'credit_card', 'bca_va', 'bni_va', 'bri_va', 'permata_va'],
            ];

            $snapToken = Snap::getSnapToken($params);

            // UPDATED: Store pending transaction with user details
            $pendingTransaction = [
                'userId' => $firebaseUid,
                'orderId' => $orderId,
                'amount' => 49000,
                'timestamp' => Carbon::now()->getTimestampMs(),
                'status' => 'pending',
                'subscriptionType' => 'premium_monthly',
                'email' => $user->email ?? null, // ADDED
                'displayName' => $user->name ?? null  // ADDED
            ];

            $this->database->getReference("pending_transactions/{$orderId}")->set($pendingTransaction);

            return response()->json([
                'snap_token' => $snapToken,
                'order_id' => $orderId
            ]);

        } catch (\Exception $e) {
            report($e);
            return response()->json(['error' => 'Failed to create payment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle Midtrans notification/callback
     */
    public function handleNotification(Request $request)
    {
        try {
            $notification = new \Midtrans\Notification();

            $transactionStatus = $notification->transaction_status;
            $orderId = $notification->order_id;
            $fraudStatus = $notification->fraud_status ?? 'accept';

            // Get pending transaction
            $pendingTxn = $this->database->getReference("pending_transactions/{$orderId}")->getValue();

            if (!$pendingTxn) {
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            $firebaseUid = $pendingTxn['userId'];

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    // UPDATED: Pass $pendingTxn to the function
                    $this->activateSubscription($firebaseUid, $orderId, $notification, $pendingTxn);
                }
            } elseif ($transactionStatus == 'settlement') {
                // UPDATED: Pass $pendingTxn to the function
                $this->activateSubscription($firebaseUid, $orderId, $notification, $pendingTxn);
            } elseif ($transactionStatus == 'pending') {
                // Update status to pending
                $this->updateTransactionStatus($orderId, 'pending', $notification);
            } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                // Use 'failed' for deny/expire/cancel to be clear
                $this->updateTransactionStatus($orderId, 'failed', $notification);
            }

            return response()->json(['message' => 'Notification handled']);

        } catch (\Exception $e) {
            report($e);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Activate subscription after successful payment
     */
    // UPDATED: Added $pendingTxn to the signature
    private function activateSubscription($firebaseUid, $orderId, $notification, $pendingTxn)
    {
        $start = Carbon::now();
        $expiry = $start->copy()->addMonth();
        $expiryMs = $expiry->getTimestampMs(); // Get expiry timestamp

        $subscriptionData = [
            'userId' => $firebaseUid,
            'subscriptionType' => 'premium_monthly',
            'paymentMethod' => $notification->payment_type ?? 'midtrans',
            'startDate' => $start->getTimestampMs(),
            'expiryDate' => $expiryMs, // Use the variable
            'active' => true,
            'transactionId' => $notification->transaction_id,
            'orderId' => $orderId,
        ];

        $this->database->getReference("subscriptions/{$firebaseUid}")->set($subscriptionData);

        // UPDATED: Record transaction to match your JSON example
        $transaction = [
            'userId' => $firebaseUid,
            'orderId' => $orderId,
            'transactionId' => $notification->transaction_id,
            'amount' => $pendingTxn['amount'] ?? 49000,
            'paymentMethod' => $notification->payment_type ?? 'midtrans',
            'timestamp' => Carbon::now()->getTimestampMs(),
            'status' => 'success',
            'subscriptionType' => 'premium_monthly',
            'email' => $pendingTxn['email'] ?? null,
            'displayName' => $pendingTxn['displayName'] ?? null,
            'expiryDate' => $expiryMs // ADDED expiry date to transaction log
        ];

        $this->database->getReference('transaction_history')->push($transaction);

        // Remove pending transaction
        $this->database->getReference("pending_transactions/{$orderId}")->remove();
    }

    /**
     * Update transaction status
     */
    private function updateTransactionStatus($orderId, $status, $notification)
    {
        $pendingTxn = $this->database->getReference("pending_transactions/{$orderId}")->getValue();
        
        if ($pendingTxn) {
            
            // UPDATED: Record transaction to match your JSON example
            $transaction = [
                'userId' => $pendingTxn['userId'],
                'orderId' => $orderId,
                'transactionId' => $notification->transaction_id ?? '',
                'amount' => $pendingTxn['amount'] ?? 49000,
                'paymentMethod' => $notification->payment_type ?? 'midtrans',
                'timestamp' => Carbon::now()->getTimestampMs(),
                'status' => $status, // This will be 'pending' or 'failed'
                'subscriptionType' => 'premium_monthly',
                'email' => $pendingTxn['email'] ?? null,
                'displayName' => $pendingTxn['displayName'] ?? null
            ];

            $this->database->getReference('transaction_history')->push($transaction);

            // Only remove pending if it's a final 'failed' state
            if ($status == 'failed') {
                $this->database->getReference("pending_transactions/{$orderId}")->remove();
            }
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

        $user = Auth::user(); // Get the logged-in user
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
            $subscription['cancelledAt'] = Carbon::now()->getTimestampMs();
            $subscriptionRef->set($subscription);

            // UPDATED: Record transaction to match your JSON example
            $transaction = [
                'userId' => $firebaseUid,
                'amount' => 0,
                'paymentMethod' => $subscription['paymentMethod'] ?? '-',
                'timestamp' => Carbon::now()->getTimestampMs(),
                'status' => 'cancelled',
                'subscriptionType' => $subscription['subscriptionType'] ?? 'premium_monthly',
                'email' => $user->email, // Added from Auth user
                'displayName' => $user->name, // Added from Auth user
                'orderId' => $subscription['orderId'] ?? null,
                'transactionId' => $subscription['transactionId'] ?? null
            ];

            $this->database->getReference('transaction_history')->push($transaction);

            return redirect()->route('subscription.show')->with('success', 'Subscription cancelled successfully.');
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to cancel subscription: ' . $e->getMessage());
        }
    }
}