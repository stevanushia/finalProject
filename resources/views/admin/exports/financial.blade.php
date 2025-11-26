@extends('admin.exports.layout')
@section('title', 'Financial Report')

@section('kpi')
    <div class="card"><small>Total Revenue</small><h3>Rp {{ number_format($data['summary']['total_revenue']) }}</h3></div>
    <div class="card"><small>Transactions</small><h3>{{ $data['summary']['total_txns'] }}</h3></div>
    <div class="card"><small>Success Rate</small><h3>{{ $data['summary']['success_rate'] }}%</h3></div>
@endsection

@section('table')
    <thead>
        <tr>
            <th>Date</th>
            <th>User</th>
            <th>Type</th>
            <th>Method</th>
            <th>Amount</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['table'] as $txn)
        <tr>
            <td>
                {{ isset($txn['timestamp']) ? date('Y-m-d H:i', $txn['timestamp']/1000) : '-' }}
            </td>
            <td>{{ $txn['displayName'] ?? 'Unknown User' }}</td>
            <td>{{ $txn['subscriptionType'] ?? '-' }}</td>
            <td>{{ $txn['paymentMethod'] ?? '-' }}</td>
            <td>Rp {{ number_format($txn['amount'] ?? 0) }}</td>
            {{-- THE FIX IS HERE: --}}
            <td>{{ strtoupper($txn['status'] ?? 'UNKNOWN') }}</td>
        </tr>
        @endforeach
    </tbody>
@endsection