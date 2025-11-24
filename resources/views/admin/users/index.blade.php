@extends('layouts.admin')

@section('title', 'User Management')
@section('header', 'Master Users')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold m-0">Registered Users</h6>
        <span class="badge bg-primary">{{ count($userList) }} Users</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="usersTable" class="table table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Status</th>
                        <th>Membership</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($userList as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($user['photoUrl'])
                                        <img src="{{ $user['photoUrl'] }}" class="rounded-circle me-3" width="40" height="40">
                                    @else
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold text-secondary" style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($user['displayName'] ?? $user['email'], 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold">{{ $user['displayName'] ?? 'No Name' }}</div>
                                        <div class="text-muted small">{{ $user['email'] }}</div>
                                        <div class="text-muted bg-light px-1 rounded border" style="font-size: 0.65rem;">{{ $user['uid'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user['disabled'])
                                    <span class="badge bg-danger">BANNED</span>
                                @else
                                    <span class="badge bg-success">ACTIVE</span>
                                @endif
                            </td>
                            <td>
                                @if($user['isPremium'])
                                    <span class="badge bg-warning text-dark"><i class="fas fa-crown me-1"></i> Premium</span>
                                @else
                                    <span class="badge bg-secondary">Free</span>
                                @endif
                            </td>
                            <td>
                                @if($user['isAdmin'])
                                    <span class="fw-bold text-danger">Admin</span>
                                @else
                                    <span class="text-muted">User</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $user['createdAt'] }}</small>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Manage
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <form action="{{ route('admin.users.toggle', $user['uid']) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item {{ $user['disabled'] ? 'text-success' : 'text-warning' }}">
                                                    @if($user['disabled'])
                                                        <i class="fas fa-check-circle me-2"></i> Enable Account
                                                    @else
                                                        <i class="fas fa-ban me-2"></i> Disable Account
                                                    @endif
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger" onclick="confirmDeleteUser('{{ $user['uid'] }}')">
                                                <i class="fas fa-trash me-2"></i> Delete Permanently
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                
                                <form id="delete-form-{{ $user['uid'] }}" action="{{ route('admin.users.delete', $user['uid']) }}" method="POST" class="d-none">
                                    @csrf @method('DELETE')
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            "order": [[ 4, "desc" ]], // Sort by Joined Date
            "pageLength": 10
        });
    });

    function confirmDeleteUser(uid) {
        Swal.fire({
            title: 'Delete User?',
            text: "This will remove them from Firebase Auth AND the Database. This cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete user!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + uid).submit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success')) Swal.fire({ icon: 'success', title: 'Success', text: "{{ session('success') }}", toast: true, position: 'top-end', timer: 3000, showConfirmButton: false }); @endif
        @if(session('error')) Swal.fire({ icon: 'error', title: 'Error', text: "{{ session('error') }}", toast: true, position: 'top-end', timer: 4000, showConfirmButton: false }); @endif
    });
</script>
@endpush