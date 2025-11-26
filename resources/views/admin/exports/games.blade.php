@extends('admin.exports.layout')
@section('title', 'Game Session Report')

@section('kpi')
    <div class="card">
        <small>Total Sessions</small>
        <h3>{{ number_format($data['counts']['total'] ?? 0) }}</h3>
    </div>
    <div class="card">
        <small>Completion Rate</small>
        <h3>
            @if(($data['counts']['total'] ?? 0) > 0)
                {{ round((($data['counts']['completed'] ?? 0) / $data['counts']['total']) * 100) }}%
            @else
                0%
            @endif
        </h3>
    </div>
    <div class="card">
        <small>Avg Total Pts</small>
        <h3>{{ $data['counts']['avg_score'] ?? 0 }}</h3>
    </div>
    <div class="card">
        <small>High Score</small>
        <h3>{{ $data['counts']['high_score'] ?? 0 }}</h3>
    </div>
@endsection

@section('table')
    <thead>
        <tr>
            <th>Session Name</th>
            <th>Teams</th>
            <th>Score</th>
            <th>Status</th>
            <th>Last Active</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['table'] as $game)
        <tr>
            <td>{{ $game['name'] ?? 'Untitled' }}</td>
            <td>{{ $game['home'] ?? 'HOME' }} vs {{ $game['away'] ?? 'AWAY' }}</td>
            <td>{{ $game['score'] ?? '0 - 0' }}</td>
            <td>{{ strtoupper($game['status'] ?? 'UNKNOWN') }}</td>
            <td>
                {{ !empty($game['timestamp']) ? date('Y-m-d H:i', $game['timestamp']/1000) : '-' }}
            </td>
        </tr>
        @endforeach
    </tbody>
@endsection