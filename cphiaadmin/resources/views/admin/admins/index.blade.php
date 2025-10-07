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
                <a href="{{ route('admin.admins.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Admin
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="admins-table">
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
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
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
                                    <small class="text-muted">Leave blank to keep current password (when editing)</small>
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
                    <button type="button" class="btn btn-primary" id="save-admin">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let table = null;
    let roles = [];

    // Load roles
    $.ajax({
        url: '{{ url("api/admins/roles") }}',
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        success: function(response) {
            roles = response.roles;
            roles.forEach(role => {
                $('#role').append(`<option value="${role.value}">${role.label}</option>`);
            });
        }
    });

    function loadTable() {
        if (table) {
            table.destroy();
        }

        table = $('#admins-table').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '{{ url("api/admins") }}',
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                dataSrc: 'data',
                error: function(xhr, error, thrown) {
                    console.error('AJAX Error:', error);
                    console.error('Status:', xhr.status);
                    console.error('Response:', xhr.responseText);
                    alert('Error loading admin users! Check browser console for details.');
                }
            },
            columns: [
                { data: 'id' },
                { data: 'username' },
                { data: 'full_name' },
                { data: 'email' },
                {
                    data: 'role',
                    render: function(data) {
                        return '<span class="badge badge-primary">' + data + '</span>';
                    }
                },
                {
                    data: 'is_active',
                    render: function(data) {
                        return data ? '<span class="badge badge-success">Active</span>' :
                               '<span class="badge badge-danger">Inactive</span>';
                    }
                },
                { data: 'last_login' },
                {
                    data: null,
                    orderable: false,
                    render: function(data) {
                        return `
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-info edit-btn" data-id="${data.id}"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-${data.is_active ? 'warning' : 'success'} toggle-btn" data-id="${data.id}">${data.is_active ? 'Deactivate' : 'Activate'}</button>
                                <button class="btn btn-danger delete-btn" data-id="${data.id}"><i class="fas fa-trash"></i></button>
                            </div>
                        `;
                    }
                }
            ],
            order: [[0, 'desc']],
            pageLength: 10
        });

        // Bind buttons
        $('#admins-table').on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            loadAdminForEdit(id);
        });

        $('#admins-table').on('click', '.toggle-btn', function() {
            const id = $(this).data('id');
            if (confirm('Are you sure you want to toggle this admin status?')) {
                $.ajax({
                    url: `{{ url("api/admins") }}/${id}/toggle-active`,
                    type: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        loadTable();
                        alert(response.message);
                    }
                });
            }
        });

        $('#admins-table').on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            if (confirm('Are you sure you want to delete this admin?')) {
                $.ajax({
                    url: `{{ url("api/admins") }}/${id}`,
                    type: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        loadTable();
                        alert(response.message);
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON.message || 'Error deleting admin!');
                    }
                });
            }
        });
    }

    loadTable();

    // Show create modal
    $('.btn-primary[href="{{ route('admin.admins.create') }}"]').click(function(e) {
        e.preventDefault();
        $('#modalTitle').text('Add Admin User');
        $('#admin-id').val('');
        $('#adminForm')[0].reset();
        $('#pwd-required').show();
        $('#password').prop('required', true);
        $('#adminModal').modal('show');
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
                loadTable();
                alert(response.message);
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                let message = 'Validation errors:\n';
                for (let field in errors) {
                    message += errors[field].join('\n') + '\n';
                }
                alert(message);
            }
        });
    });

    // Load admin for editing
    function loadAdminForEdit(id) {
        $.ajax({
            url: `{{ url("api/admins") }}/${id}`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            success: function(response) {
                const admin = response.admin;
                $('#modalTitle').text('Edit Admin User');
                $('#admin-id').val(admin.id);
                $('#username').val(admin.username);
                $('#email').val(admin.email);
                $('#full_name').val(admin.full_name);
                $('#role').val(admin.role);
                $('#is_active').val(admin.is_active ? '1' : '0');
                $('#password').val('');
                $('#password_confirmation').val('');
                $('#pwd-required').hide();
                $('#password').prop('required', false);
                $('#adminModal').modal('show');
            },
            error: function(xhr) {
                alert('Error loading admin data!');
            }
        });
    }
});
</script>
@endpush

