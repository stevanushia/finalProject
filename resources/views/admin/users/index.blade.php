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
        <div class="table-responsive" style="min-height: 400px;"> {{-- Min-height helps dropdowns have space --}}
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
                                        {{-- Use text-truncate to prevent massive UIDs breaking layout --}}
                                        <div class="text-muted bg-light px-1 rounded border text-truncate" style="font-size: 0.65rem; max-width: 150px;">
                                            {{ $user['uid'] }}
                                        </div>
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
                                    {{-- FIX 1: data-bs-display="static" prevents the dropdown from being hidden by the table --}}
                                    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                        Manage
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        {{-- EDIT BUTTON --}}
                                        <li>
                                            {{-- FIX 2: Pass data via data-attribute instead of direct onclick --}}
                                            <button type="button" class="dropdown-item" 
                                                    data-user-info="{{ json_encode($user) }}"
                                                    onclick="editUser(this)">
                                                <i class="fas fa-edit me-2 text-primary"></i> Edit Details
                                            </button>
                                        </li>
                                        
                                        {{-- TOGGLE STATUS --}}
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
                                        {{-- DELETE --}}
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

{{-- EDIT USER MODAL (Kept same, just ensures IDs match) --}}
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editUserForm" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2"></i>Edit User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Display Name</label>
                        <input type="text" name="displayName" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Email Address</label>
                        <input type="email" name="email" id="editEmail" class="form-control" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Role</label>
                            <select name="role" id="editRole" class="form-select">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Membership</label>
                            <select name="membership" id="editMembership" class="form-select">
                                <option value="free">Free</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">Reset Password <span class="text-muted fw-normal">(Optional)</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold">Save Changes</button>
                </div>
            </form>
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
            "order": [[ 4, "desc" ]],
            "pageLength": 10
        });
    });

    // FIX 3: Update the function to parse the data attribute safely
    function editUser(buttonElement) {
        // Get the JSON string from the data-attribute and parse it
        const user = JSON.parse(buttonElement.getAttribute('data-user-info'));

        // Populate form fields
        document.getElementById('editName').value = user.displayName || '';
        document.getElementById('editEmail').value = user.email || '';
        document.getElementById('editRole').value = user.isAdmin ? 'admin' : 'user';
        document.getElementById('editMembership').value = user.isPremium ? 'premium' : 'free';
        
        // Set form action URL dynamically
        const form = document.getElementById('editUserForm');
        form.action = `/admin/users/${user.uid}/update`;

        // Show modal
        new bootstrap.Modal(document.getElementById('editUserModal')).show();
    }

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