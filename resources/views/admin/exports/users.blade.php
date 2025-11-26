@extends('admin.exports.layout')
@section('title', 'User Report')

@section('kpi')
    <div class="card"><small>Total Users</small><h3>{{ $data['counts']['total'] ?? 0 }}</h3></div>
    <div class="card"><small>Premium</small><h3>{{ $data['counts']['premium'] ?? 0 }}</h3></div>
    <div class="card"><small>Active (30d)</small><h3>{{ $data['counts']['active'] ?? 0 }}</h3></div>
@endsection

@section('table')
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Last Active</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['table'] as $user)
        <tr>
            <td>{{ $user['name'] ?? 'Unknown' }}</td>
            <td>{{ $user['email'] ?? '-' }}</td>
            <td>{{ $user['role'] ?? 'User' }}</td>
            <td>{{ $user['status'] ?? 'Free' }}</td>
            <td>
                {{ (!empty($user['last_active'])) ? date('Y-m-d', $user['last_active']/1000) : 'Never' }}
            </td>
        </tr>
        @endforeach
    </tbody>
@endsection