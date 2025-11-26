@extends('layouts.admin')

@section('title', 'Master Tournaments')
@section('header', 'Manage Tournaments')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold m-0">All Tournaments</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tournamentsTable" class="table table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Teams</th>
                        <th>Start Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tournaments as $id => $t)
                        <tr>
                            <td class="fw-bold">{{ $t['name'] ?? 'Untitled' }}</td>
                            <td>
                                <span class="badge bg-{{ ($t['status']??'')=='completed'?'success':(($t['status']??'')=='ongoing'?'warning':'secondary') }} text-uppercase">
                                    {{ $t['status'] ?? 'upcoming' }}
                                </span>
                            </td>
                            <td>{{ $t['participantCount'] ?? 0 }}</td>
                            <td>
                                @if(!empty($t['startDate']))
                                    {{ \Carbon\Carbon::createFromTimestampMs($t['startDate'])->format('d M Y') }}
                                @else - @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.tournaments.edit', $id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('tournaments.show', $id) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                                    <i class="fas fa-sitemap"></i> Bracket
                                </a>
                                <form action="{{ route('admin.tournaments.delete', $id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this tournament?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush
@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() { $('#tournamentsTable').DataTable(); });
</script>
@endpush