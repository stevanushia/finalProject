<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Log;

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

            // Store pending transaction with user details
            $pendingTransaction = [
                'userId' => $firebaseUid,
                'orderId' => $orderId,
                'amount' => 49000,
                'timestamp' => Carbon::now()->getTimestampMs(),
                'status' => 'pending',
                'subscriptionType' => 'premium_monthly',
                'email' => $user->email ?? null,
                'displayName' => $user->name ?? null
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
        // 1. Log the raw request from Midtrans
        Log::info('Midtrans Notification Received:', $request->all());

        try {
            $notification = new \Midtrans\Notification();

            // 2. Log the notification object
            Log::info('Midtrans Notification Object:', (array) $notification);

            $transactionStatus = $notification->transaction_status;
            $orderId = $notification->order_id;
            $fraudStatus = $notification->fraud_status ?? 'accept';

            // 3. Log the key details we are about to check
            Log::info('Processing Notification:', [
                'order_id' => $orderId,
                'status' => $transactionStatus,
                'fraud_status' => $fraudStatus
            ]);

            // Get pending transaction
            $pendingTxn = $this->database->getReference("pending_transactions/{$orderId}")->getValue();

            if (!$pendingTxn) {
                // 4. THIS IS THE MOST LIKELY PROBLEM. We will now see a log for it.
                Log::warning('Pending transaction not found for Order ID:', ['order_id' => $orderId]);
                
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            // 5. Log the user we found
            Log::info('Pending transaction FOUND:', $pendingTxn);

            $firebaseUid = $pendingTxn['userId'];

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    Log::info('Activating subscription (capture):', ['order_id' => $orderId]);
                    $this->activateSubscription($firebaseUid, $orderId, $notification, $pendingTxn);
                }
            } elseif ($transactionStatus == 'settlement') {
                Log::info('Activating subscription (settlement):', ['order_id' => $orderId]);
                $this->activateSubscription($firebaseUid, $orderId, $notification, $pendingTxn);
            } elseif ($transactionStatus == 'pending') {
                Log::info('Updating transaction to pending:', ['order_id' => $orderId]);
                $this->updateTransactionStatus($orderId, 'pending', $notification);
            } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                Log::info('Updating transaction to failed:', ['order_id' => $orderId]);
                $this->updateTransactionStatus($orderId, 'failed', $notification);
            }

            return response()->json(['message' => 'Notification handled']);

        } catch (\Exception $e) {
            // 6. If something ELSE goes wrong, it will be logged here.
            Log::error('Error in handleNotification:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    // --- THIS IS THE START OF THE CLEANED-UP SECTION ---

    /**
     * Update transaction status
     */
    private function updateTransactionStatus($orderId, $status, $notification)
    {
        // ADDED LOGGING
        Log::info('Inside updateTransactionStatus', ['order_id' => $orderId, 'status' => $status]);
        try {
            $pendingTxn = $this->database->getReference("pending_transactions/{$orderId}")->getValue();
            
            if ($pendingTxn) {
                
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

                Log::info('Pushing to transaction_history (non-success)...', $transaction); // <-- ADDED
                $this->database->getReference('transaction_history')->push($transaction);
                Log::info('...Transaction_history PUSHED successfully.'); // <-- ADDED

                // Only remove pending if it's a final 'failed' state
                if ($status == 'failed') {
                    Log::info('Removing pending transaction (failed)...', ['order_id' => $orderId]); // <-- ADDED
                    $this->database->getReference("pending_transactions/{$orderId}")->remove();
                    Log::info('...Pending transaction REMOVED successfully.'); // <-- ADDED
                }
            } else {
                Log::warning('Pending transaction not found in updateTransactionStatus', ['order_id' => $orderId]); // <-- ADDED
            }
        } catch (\Exception $e) {
            Log::error('CRITICAL ERROR in updateTransactionStatus:', [ // <-- ADDED
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * Activate subscription after successful payment
     */
    private function activateSubscription($firebaseUid, $orderId, $notification, $pendingTxn)
    {
        Log::info('Inside activateSubscription', ['order_id' => $orderId]); 
        try {
            $start = Carbon::now();
            $expiry = $start->copy()->addMonth();
            $expiryMs = $expiry->getTimestampMs();

            $subscriptionData = [
                'userId' => $firebaseUid,
                'subscriptionType' => 'premium_monthly',
                'paymentMethod' => $notification->payment_type ?? 'midtrans',
                'startDate' => $start->getTimestampMs(),
                'expiryDate' => $expiryMs, 
                'active' => true,
                'transactionId' => $notification->transaction_id,
                'orderId' => $orderId,
            ];

            Log::info('1. Setting subscription data...'); 
            $this->database->getReference("subscriptions/{$firebaseUid}")->set($subscriptionData);
            Log::info('...Subscription data SET successfully.'); 

            // --- THIS IS THE FIX ---
            Log::info('2. Updating users node...'); 
            $this->database->getReference("users/{$firebaseUid}")->update([
                'isPremium' => true,
                'lastUpdated' => Carbon::now()->getTimestampMs()
            ]);
            Log::info('...Users node UPDATED successfully.'); 
            // --- END OF FIX ---

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
                'expiryDate' => $expiryMs
            ];

            Log::info('3. Pushing to transaction_history...'); 
            $this->database->getReference('transaction_history')->push($transaction);
            Log::info('...Transaction_history PUSHED successfully.'); 

            Log::info('4. Removing pending transaction...'); 
            $this->database->getReference("pending_transactions/{$orderId}")->remove();
            Log::info('...Pending transaction REMOVED successfully.'); 

        } catch (\Exception $e) {
            Log::error('CRITICAL ERROR in activateSubscription:', [ 
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
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
        
        // ADDED Auth user for email/name
        $user = Auth::user(); 
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
            
            Log::info('1. Cancelling subscription data...');
            $subscriptionRef->set($subscription);
            Log::info('...Subscription data CANCELLED successfully.');

            // --- THIS IS THE FIX ---
            Log::info('2. Updating users node to false...'); 
            $this->database->getReference("users/{$firebaseUid}")->update([
                'isPremium' => false,
                'lastUpdated' => Carbon::now()->getTimestampMs()
            ]);
            Log::info('...Users node UPDATED to false successfully.'); 
            // --- END OF FIX ---

            // --- UPDATED TRANSACTION DATA ---
            $transaction = [
                'userId' => $firebaseUid,
                'orderId' => $subscription['orderId'] ?? null,
                'transactionId' => $subscription['transactionId'] ?? null,
                'amount' => 0,
                'paymentMethod' => $subscription['paymentMethod'] ?? '-',
                'timestamp' => Carbon::now()->getTimestampMs(),
                'status' => 'cancelled',
                'subscriptionType' => $subscription['subscriptionType'] ?? 'premium_monthly',
                'email' => $user->email, // Added
                'displayName' => $user->name // Added
            ];
            
            Log::info('3. Pushing cancel to transaction_history...'); 
            $this->database->getReference('transaction_history')->push($transaction);
            Log::info('...Transaction_history PUSHED successfully.'); 

            return redirect()->route('subscription.show')->with('success', 'Subscription cancelled successfully.');
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to cancel subscription: ' . $e->getMessage());
        }
    }
}