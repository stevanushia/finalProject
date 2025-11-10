<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Plans</title>
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
        .status-banner {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .plan-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }
        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }
        .premium-badge {
            position: absolute;
            top: -15px;
            right: 20px;
            background: #ffc107;
            color: #000;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .price {
            font-size: 3rem;
            font-weight: bold;
            margin: 1rem 0;
        }
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 2rem 0;
        }
        .feature-list li {
            padding: 0.75rem 0;
            display: flex;
            align-items: center;
        }
        .feature-list li i {
            width: 24px;
            margin-right: 12px;
            font-size: 1.1rem;
        }
        .btn-subscribe {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .btn-subscribe:hover {
            transform: scale(1.02);
        }
        .transaction-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-top: 3rem;
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
    </style>
</head>
<body>
    @php
        use Carbon\Carbon;
        $isPremium = !empty($subscription['subscriptionType']) && str_contains($subscription['subscriptionType'], 'premium');
        $isExpired = isset($subscription['expiryDate']) && Carbon::createFromTimestampMs($subscription['expiryDate'])->isPast();
        $active = $subscription['active'] ?? false;
    @endphp

    <div class="main-container">
        <!-- Header -->
        <div class="text-center text-white mb-5">
            <h1 class="display-4 fw-bold mb-2">Choose Your Plan</h1>
            <p class="lead">Unlock premium features and take your experience to the next level</p>
        </div>

        <!-- Status Banner -->
        @if($active && !$isExpired)
            <div class="status-banner">
                <div class="d-flex align-items-center">
                    <i class="fas fa-crown text-warning fa-3x me-3"></i>
                    <div>
                        <h4 class="mb-1 text-success">Premium Member</h4>
                        <p class="mb-0 text-muted">Your subscription expires on {{ Carbon::createFromTimestampMs($subscription['expiryDate'])->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>
        @elseif($isExpired)
            <div class="status-banner">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle text-warning fa-3x me-3"></i>
                    <div>
                        <h4 class="mb-1 text-warning">Subscription Expired</h4>
                        <p class="mb-0 text-muted">Renew now to continue enjoying premium features</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Plans -->
        <div class="row g-4 mb-4">
            <!-- Free Plan -->
            <div class="col-lg-6">
                <div class="plan-card">
                    <div class="text-center">
                        <h3 class="fw-bold mb-3">Free Plan</h3>
                        <div class="price text-secondary">Rp 0</div>
                        <p class="text-muted">Forever free</p>
                    </div>

                    <ul class="feature-list">
                        <li>
                            <i class="fas fa-check text-success"></i>
                            <span>Basic game statistics</span>
                        </li>
                        <li>
                            <i class="fas fa-check text-success"></i>
                            <span>Up to 5 games stored</span>
                        </li>
                        <li>
                            <i class="fas fa-check text-success"></i>
                            <span>Standard analytics dashboard</span>
                        </li>
                        <li>
                            <i class="fas fa-check text-success"></i>
                            <span>Basic performance metrics</span>
                        </li>
                        <li class="text-muted">
                            <i class="fas fa-times text-secondary"></i>
                            <span>Advanced insights</span>
                        </li>
                        <li class="text-muted">
                            <i class="fas fa-times text-secondary"></i>
                            <span>Unlimited storage</span>
                        </li>
                        <li class="text-muted">
                            <i class="fas fa-times text-secondary"></i>
                            <span>Priority support</span>
                        </li>
                    </ul>

                    @if(!$active || $isExpired)
                        <button class="btn btn-outline-secondary btn-subscribe" disabled>Current Plan</button>
                    @endif
                </div>
            </div>

            <!-- Premium Plan -->
            <div class="col-lg-6">
                <div class="plan-card">
                    @if($active && !$isExpired)
                        <span class="premium-badge">
                            <i class="fas fa-star me-1"></i>ACTIVE
                        </span>
                    @else
                        <span class="premium-badge">
                            <i class="fas fa-star me-1"></i>POPULAR
                        </span>
                    @endif

                    <div class="text-center">
                        <h3 class="fw-bold mb-3">Premium Plan</h3>
                        <div class="price text-warning">Rp 49K</div>
                        <p class="text-muted">Billed monthly</p>
                    </div>

                    <ul class="feature-list">
                        <li>
                            <i class="fas fa-check text-warning"></i>
                            <span><strong>All Free features, plus:</strong></span>
                        </li>
                        <li>
                            <i class="fas fa-check text-warning"></i>
                            <span>Unlimited game storage</span>
                        </li>
                        <li>
                            <i class="fas fa-check text-warning"></i>
                            <span>Advanced analytics & insights</span>
                        </li>
                        <li>
                            <i class="fas fa-check text-warning"></i>
                            <span>Performance predictions AI</span>
                        </li>
                        <li>
                            <i class="fas fa-check text-warning"></i>
                            <span>Export to CSV/PDF</span>
                        </li>
                        <li>
                            <i class="fas fa-check text-warning"></i>
                            <span>Priority customer support</span>
                        </li>
                        <li>
                            <i class="fas fa-check text-warning"></i>
                            <span>Ad-free experience</span>
                        </li>
                    </ul>

                    @if($active && !$isExpired)
                        <form method="POST" action="">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-subscribe">
                                <i class="fas fa-times me-2"></i>Cancel Subscription
                            </button>
                        </form>
                    @else
                        <form method="POST" action="">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-subscribe">
                                <i class="fas fa-crown me-2"></i>Subscribe Now
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>