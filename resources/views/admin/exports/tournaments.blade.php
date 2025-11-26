@extends('admin.exports.layout')
@section('title', 'Tournament Report')

@section('kpi')
    <div class="card">
        <small>Total Tournaments</small>
        <h3>{{ number_format($data['counts']['total'] ?? 0) }}</h3>
    </div>
    <div class="card">
        <small>Active</small>
        <h3>{{ number_format($data['counts']['active'] ?? 0) }}</h3>
    </div>
    <div class="card">
        <small>Participants</small>
        <h3>{{ number_format($data['counts']['total_participants'] ?? 0) }}</h3>
    </div>
    <div class="card">
        <small>Popular Format</small>
        <h3>{{ $data['counts']['popular_format'] ?? 0 }} Teams</h3>
    </div>
@endsection

@section('table')
    <thead>
        <tr>
            <th>Tournament Name</th>
            <th>Size</th>
            <th>Start Date</th>
            <th>Status</th>
            <th>Winner</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['table'] as $t)
        <tr>
            <td>{{ $t['name'] ?? 'Untitled' }}</td>
            <td>{{ $t['teams'] ?? 0 }} Teams</td>
            <td>
                {{ !empty($t['start_date']) ? date('Y-m-d', $t['start_date']/1000) : 'TBD' }}
            </td>
            <td>{{ strtoupper($t['status'] ?? 'UPCOMING') }}</td>
            <td>{{ $t['winner'] ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
@endsection