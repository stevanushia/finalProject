<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: "Poppins", sans-serif;
        }

        .profile-header {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            color: white;
            padding: 3rem 2rem;
            border-radius: 15px;
            text-align: center;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid white;
            object-fit: cover;
            margin-bottom: 1rem;
        }

        .badge-premium {
            background-color: gold;
            color: black;
            font-weight: bold;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            border-radius: 30px;
            padding: 8px 16px;
        }

        .card {
            border: none;
            border-radius: 12px;
        }

        .card-header {
            border-top-left-radius: 12px !important;
            border-top-right-radius: 12px !important;
        }
    </style>
</head>

<body>
    <!-- Back to Home Button -->
    <div class="position-relative">
        <a href="{{ url('/') }}" class="btn btn-outline-primary back-btn">
            <i class="fas fa-arrow-left me-2"></i> Back to Home
        </a>
    </div>

    <!-- Profile Header -->
    <div class="container my-5">
        <div class="profile-header shadow position-relative">
            <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" class="profile-avatar" alt="User Avatar">
            <h2 class="mb-0">{{ $user->name ?? 'Player' }}</h2>
            <p class="lead">{{ $user->email }}</p>
            @if(!empty($subscription['subscriptionType']) && str_contains($subscription['subscriptionType'], 'premium'))
                <span class="badge badge-premium">PREMIUM</span>
            @else
                <span class="badge bg-secondary">FREE USER</span>
            @endif
        </div>
    </div>

    <!-- Profile Details -->
    <div class="container mb-5">
        <div class="row g-4">
            <!-- Account Info -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-user me-2"></i> Account Information
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> {{ $user->name }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        @if(!empty($firebaseUser))
                            <p><strong>Firebase UID:</strong> {{ $firebaseUser->uid }}</p>
                            <p><strong>Email Verified:</strong> 
                                <span class="badge {{ ($firebaseUser->emailVerified ?? false) ? 'bg-success' : 'bg-danger' }}">
                                    {{ ($firebaseUser->emailVerified ?? false) ? 'Yes' : 'No' }}
                                </span>
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Subscription Info -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <i class="fas fa-crown me-2"></i> Subscription
                    </div>
                    <div class="card-body">
                        @if(!empty($subscription))
                            @php
                                $start = isset($subscription['startDate']) 
                                    ? \Carbon\Carbon::createFromTimestampMs($subscription['startDate'])->toDayDateTimeString() 
                                    : '-';
                                $expiry = isset($subscription['expiryDate']) 
                                    ? \Carbon\Carbon::createFromTimestampMs($subscription['expiryDate'])->toDayDateTimeString() 
                                    : '-';
                            @endphp
                            <p><strong>Status:</strong> {{ $subscription['active'] ? 'Active' : 'Inactive' }}</p>
                            <p><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $subscription['subscriptionType'])) }}</p>
                            <p><strong>Payment Method:</strong> {{ strtoupper($subscription['paymentMethod']) }}</p>
                            <p><strong>Start Date:</strong> {{ $start }}</p>
                            <p><strong>Expiry Date:</strong> {{ $expiry }}</p>
                        @else
                            <p class="text-muted mb-0">No active subscription found.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="container mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white d-flex justify-content-between">
                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i> Transaction History</h5>
                <small>{{ is_iterable($transactions) ? count($transactions) : 0 }} records</small>
            </div>
            <div class="card-body">
                @if(!empty($transactions) && count($transactions) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $t)
                                <tr>
                                    <td>
                                        @if(!empty($t['timestamp']))
                                            {{ \Carbon\Carbon::createFromTimestampMs($t['timestamp'])->toDayDateTimeString() }}
                                        @else
                                            {{ $t['date'] ?? '-' }}
                                        @endif
                                    </td>
                                    <td>{{ $t['item'] ?? '—' }}</td>
                                    <td>
                                        @if(!empty($t['amount']))
                                            Rp{{ number_format($t['amount'], 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ ($t['status'] ?? '') === 'success' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ ucfirst($t['status'] ?? 'pending') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No transactions found</h5>
                        <p class="text-muted">Your activity history will appear here.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4 bg-light border-top mt-5">
        <small class="text-muted">&copy; {{ date('Y') }} MyGameStats. All rights reserved.</small>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
