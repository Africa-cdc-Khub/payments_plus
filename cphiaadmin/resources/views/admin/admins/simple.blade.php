@extends('layouts.admin')

@section('title', 'Admin Users')
@section('page-title', 'Admin Users Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Admin Users</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Admin Users</h3>
            <div class="card-tools">
                <button class="btn btn-primary" data-toggle="modal" data-target="#adminModal">
                    <i class="fas fa-plus"></i> Add Admin User
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $admins = \App\Models\Admin::with('roles')->get();
                    @endphp

                    @foreach($admins as $admin)
                    <tr>
                        <td>{{ $admin->id }}</td>
                        <td>{{ $admin->username }}</td>
                        <td>{{ $admin->full_name ?? 'N/A' }}</td>
                        <td>{{ $admin->email }}</td>
                        <td>
                            <span class="badge badge-primary">
                                {{ $admin->getRoleNames()->first() ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            @if($admin->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $admin->last_login ? $admin->last_login->format('M d, Y H:i') : 'Never' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-info edit-btn" data-id="{{ $admin->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-{{ $admin->is_active ? 'warning' : 'success' }} toggle-btn" data-id="{{ $admin->id }}">
                                    {{ $admin->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button class="btn btn-danger delete-btn" data-id="{{ $admin->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit Modal (same as before) -->
    <div class="modal fade" id="adminModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modalTitle">Add Admin User</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="adminForm">
                        <input type="hidden" id="admin-id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="username" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Role <span class="text-danger">*</span></label>
                                    <select class="form-control" id="role" required>
                                        <option value="">Select Role</option>
                                        @foreach(\Spatie\Permission\Models\Role::all() as $role)
                                            <option value="{{ $role->name }}">{{ ucwords(str_replace('_', ' ', $role->name)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" id="is_active">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Password <span class="text-danger" id="pwd-required">*</span></label>
                                    <input type="password" class="form-control" id="password">
                                    <small class="text-muted">Leave blank to keep existing password when editing</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Confirm Password</label>
                                    <input type="password" class="form-control" id="password_confirmation">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="save-admin">Save Admin</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize modal for adding
    $('.btn-primary[data-target="#adminModal"]').on('click', function() {
        $('#modalTitle').text('Add Admin User');
        $('#admin-id').val('');
        $('#adminForm')[0].reset();
        $('#pwd-required').show();
        $('#password').prop('required', true);
    });

    // Save admin
    $('#save-admin').click(function() {
        const id = $('#admin-id').val();
        const data = {
            username: $('#username').val(),
            email: $('#email').val(),
            full_name: $('#full_name').val(),
            role: $('#role').val(),
            is_active: $('#is_active').val() == '1',
            password: $('#password').val(),
            password_confirmation: $('#password_confirmation').val()
        };

        const url = id ? `{{ url("api/admins") }}/${id}` : '{{ url("api/admins") }}';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: JSON.stringify(data),
            success: function(response) {
                $('#adminModal').modal('hide');
                alert(response.message);
                location.reload();
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                let message = 'Validation errors:\n';
                if (errors) {
                    for (let field in errors) {
                        message += errors[field].join('\n') + '\n';
                    }
                } else {
                    message = xhr.responseJSON?.message || 'Error saving admin!';
                }
                alert(message);
            }
        });
    });

    // Edit, toggle, delete buttons work the same way
    $('.edit-btn, .toggle-btn, .delete-btn').on('click', function() {
        alert('Working on implementing these actions...');
    });
});
</script>
@endpush

