<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Subscription Plans</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @php
    $midtrans_url = config('midtrans.is_production') 
        ? 'https://app.midtrans.com/snap/snap.js' 
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
    @endphp
    <script src="{{ $midtrans_url }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
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
        .badge-status {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
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
        <!-- Back Button -->
        <a href="{{ url('/') }}" class="back-btn">
            <i class="fas fa-arrow-left me-2"></i>Back to Home
        </a>

        <!-- Header -->
        <div class="text-center text-white mb-5">
            <h1 class="display-4 fw-bold mb-2">Choose Your Plan</h1>
            <p class="lead">Unlock premium features and take your experience to the next level</p>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

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
                            <span>Collaboration Input</span>
                        </li>
                        <li>
                            <i class="fas fa-check text-warning"></i>
                            <span>Export to CSV/PDF</span>
                        </li>
                        <li>
                            <i class="fas fa-check text-warning"></i>
                            <span>Priority customer support</span>
                        </li>
                    </ul>

                    @if($active && !$isExpired)
                        <form method="POST" action="{{ route('subscription.cancel') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-subscribe" 
                                    onclick="return confirm('Are you sure you want to cancel your subscription?')">
                                <i class="fas fa-times me-2"></i>Cancel Subscription
                            </button>
                        </form>
                    @else
                        <button id="pay-button" class="btn btn-warning btn-subscribe">
                            <i class="fas fa-crown me-2"></i>Subscribe Now
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('pay-button')?.addEventListener('click', function () {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            
            fetch('{{ route("subscription.payment") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.snap_token) {
                    snap.pay(data.snap_token, {
                        onSuccess: function(result) {
                            window.location.href = '{{ route("subscription.show") }}?payment=success';
                        },
                        onPending: function(result) {
                            window.location.href = '{{ route("subscription.show") }}?payment=pending';
                        },
                        onError: function(result) {
                            alert('Payment failed. Please try again.');
                            location.reload();
                        },
                        onClose: function() {
                            document.getElementById('pay-button').disabled = false;
                            document.getElementById('pay-button').innerHTML = '<i class="fas fa-crown me-2"></i>Subscribe Now';
                        }
                    });
                } else {
                    alert('Failed to initialize payment. Please try again.');
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                location.reload();
            });
        });
    </script>
</body>
</html>