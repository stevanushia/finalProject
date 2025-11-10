<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 1rem;
        }
        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 2rem;
        }
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateX(-5px);
        }
        .profile-header {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            position: relative;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid #667eea;
            object-fit: cover;
            margin-bottom: 1rem;
        }
        .premium-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #ffc107;
            color: #000;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .free-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #6c757d;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .info-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }
        .info-card h5 {
            color: #667eea;
            font-weight: bold;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f0f0f0;
        }
        .info-row {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #666;
            margin-bottom: 0.25rem;
        }
        .info-value {
            color: #333;
        }
        .transaction-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .transaction-row {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s ease;
        }
        .transaction-row:hover {
            background: #f8f9fa;
        }
        .transaction-row:last-child {
            border-bottom: none;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    @php
        use Carbon\Carbon;
        $isPremium = !empty($subscription['subscriptionType']) && str_contains($subscription['subscriptionType'], 'premium');
        $isExpired = isset($subscription['expiryDate']) && Carbon::createFromTimestampMs($subscription['expiryDate'])->isPast();
    @endphp

    <div class="main-container">
        <!-- Back Button -->
        <a href="{{ url('/') }}" class="back-btn">
            <i class="fas fa-arrow-left me-2"></i>Back to Home
        </a>

        <!-- Header -->
        <div class="text-center text-white mb-4">
            <h1 class="display-4 fw-bold mb-2">My Profile</h1>
            <p class="lead">Manage your account and subscription</p>
        </div>

        <!-- Profile Header -->
        <div class="profile-header">
            @if($isPremium && !$isExpired)
                <span class="premium-badge">
                    <i class="fas fa-crown me-1"></i>PREMIUM
                </span>
            @else
                <span class="free-badge">
                    <i class="fas fa-user me-1"></i>FREE
                </span>
            @endif

            <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" class="profile-avatar" alt="User Avatar">
            <h2 class="mb-2 fw-bold">{{ $user->name ?? 'Player' }}</h2>
            <p class="text-muted mb-0">{{ $user->email }}</p>
        </div>

        <!-- Info Cards -->
        <div class="row g-4 mb-4">
            <!-- Account Information -->
            <div class="col-lg-6">
                <div class="info-card">
                    <h5><i class="fas fa-user-circle me-2"></i>Account Information</h5>
                    
                    <div class="info-row">
                        <div class="info-label">Full Name</div>
                        <div class="info-value">{{ $user->name }}</div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Email Address</div>
                        <div class="info-value">{{ $user->email }}</div>
                    </div>

                    @if(!empty($firebaseUser))
                    <div class="info-row">
                        <div class="info-label">Firebase UID</div>
                        <div class="info-value">
                            <code style="font-size: 0.85rem;">{{ $firebaseUser->uid }}</code>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Email Verification</div>
                        <div class="info-value">
                            <span class="badge {{ ($firebaseUser->emailVerified ?? false) ? 'bg-success' : 'bg-danger' }}">
                                {{ ($firebaseUser->emailVerified ?? false) ? 'Verified' : 'Not Verified' }}
                            </span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Subscription Information -->
            <div class="col-lg-6">
                <div class="info-card">
                    <h5><i class="fas fa-crown me-2"></i>Subscription Details</h5>
                    
                    @if(!empty($subscription))
                        @php
                            $start = isset($subscription['startDate']) 
                                ? Carbon::createFromTimestampMs($subscription['startDate'])->format('M d, Y') 
                                : '-';
                            $expiry = isset($subscription['expiryDate']) 
                                ? Carbon::createFromTimestampMs($subscription['expiryDate'])->format('M d, Y') 
                                : '-';
                        @endphp

                        <div class="info-row">
                            <div class="info-label">Status</div>
                            <div class="info-value">
                                @if(!$isPremium)
                                    <span class="badge bg-secondary">Free Plan</span>
                                @elseif($isExpired)
                                    <span class="badge bg-danger">Expired</span>
                                @elseif($subscription['active'])
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-warning text-dark">Inactive</span>
                                @endif
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Plan Type</div>
                            <div class="info-value">{{ ucfirst(str_replace('_', ' ', $subscription['subscriptionType'])) }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Payment Method</div>
                            <div class="info-value">{{ strtoupper($subscription['paymentMethod']) }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Start Date</div>
                            <div class="info-value">{{ $start }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Expiry Date</div>
                            <div class="info-value">{{ $expiry }}</div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No active subscription found</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="transaction-card">
            <h4 class="fw-bold mb-4">
                <i class="fas fa-receipt me-2"></i>Transaction History
                <small class="text-muted ms-2">({{ is_iterable($transactions) ? count($transactions) : 0 }} records)</small>
            </h4>
            
            @if(!empty($transactions) && count($transactions) > 0)
                @foreach($transactions as $t)
                @php
                    $status = strtolower($t['status'] ?? 'pending');
                    $statusClass = match($status) {
                        'success', 'succeeded', 'paid' => 'bg-success',
                        'cancelled' => 'bg-secondary',
                        'failed', 'error' => 'bg-danger',
                        default => 'bg-warning text-dark',
                    };
                @endphp
                <div class="transaction-row">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <div class="info-label">Date</div>
                            <div class="info-value">
                                @if(!empty($t['timestamp']))
                                    {{ Carbon::createFromTimestampMs($t['timestamp'])->format('M d, Y') }}
                                @else
                                    {{ $t['date'] ?? '-' }}
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Description</div>
                            <div class="info-value fw-bold">{{ $t['item'] ?? '-' }}</div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-label">Amount</div>
                            <div class="info-value fw-bold text-success">
                                @if(!empty($t['amount']))
                                    Rp{{ number_format($t['amount'], 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-label">Method</div>
                            <div class="info-value">
                                <small class="text-uppercase">{{ $t['paymentMethod'] ?? '-' }}</small>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <span class="badge {{ $statusClass }} status-badge">{{ ucfirst($status) }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No transactions yet</h5>
                    <p class="text-muted">Your transaction history will appear here</p>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>