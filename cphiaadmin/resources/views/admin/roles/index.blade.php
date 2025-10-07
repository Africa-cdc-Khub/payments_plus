@extends('layouts.admin')

@section('title', 'Role Management')
@section('page-title', 'Role & Permission Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Roles</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <!-- Roles List -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-shield"></i> System Roles</h3>
                    <div class="card-tools">
                        <button class="btn btn-sm btn-success" id="add-role-btn">
                            <i class="fas fa-plus"></i> Add Role
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-pills flex-column">
                        @foreach($roles as $role)
                        <li class="nav-item">
                            <a href="#" class="nav-link role-item {{ $loop->first ? 'active' : '' }}" data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}">
                                <i class="fas fa-shield-alt nav-icon"></i>
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                <span class="badge badge-info float-right">{{ $role->permissions->count() }} perms</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- System Roles Info -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Info</h3>
                </div>
                <div class="card-body">
                    <p class="text-sm"><strong>System Roles:</strong></p>
                    <ul class="text-sm">
                        <li>super_admin</li>
                        <li>admin</li>
                        <li>finance_team</li>
                        <li>visa_team</li>
                        <li>ticketing_team</li>
                    </ul>
                    <p class="text-sm text-muted">System roles cannot be deleted or renamed, but you can modify their permissions.</p>
                    <p class="text-sm"><strong>Custom Roles:</strong></p>
                    <p class="text-sm text-muted">You can create custom roles with specific permissions for your organization's needs.</p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Role Permissions -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-key"></i> Permissions for: <span id="current-role-name">Select a Role</span></h3>
                    <div class="card-tools" id="role-actions" style="display: none;">
                        <button class="btn btn-sm btn-warning" id="edit-role-btn">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" id="delete-role-btn">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="permissions-list">
                        <p class="text-muted">Select a role from the left to view its permissions.</p>
                    </div>
                </div>
            </div>

            <!-- All Available Permissions -->
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> All Available Permissions</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php
                            $grouped = $permissions->groupBy(function($perm) {
                                return explode('_', $perm->name)[0];
                            });
                        @endphp

                        @foreach($grouped as $category => $perms)
                        <div class="col-md-4">
                            <h6><strong>{{ ucfirst($category) }}</strong></h6>
                            <ul class="list-unstyled text-sm">
                                @foreach($perms as $perm)
                                <li><i class="fas fa-check text-success"></i> {{ $perm->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Role Modal -->
    <div class="modal fade" id="roleModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title" id="roleModalTitle">Add Role</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="roleForm">
                        <input type="hidden" id="role-id">
                        <div class="form-group">
                            <label>Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="role-name" placeholder="e.g., content_manager" required>
                            <small class="text-muted">Use lowercase with underscores (e.g., content_manager)</small>
                        </div>

                        <div class="form-group">
                            <label>Assign Permissions</label>
                            <div class="row">
                                @foreach($grouped as $category => $perms)
                                <div class="col-md-4">
                                    <h6><strong>{{ ucfirst($category) }}</strong></h6>
                                    @foreach($perms as $perm)
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox" value="{{ $perm->name }}" id="perm-{{ $perm->id }}">
                                        <label class="form-check-label text-sm" for="perm-{{ $perm->id }}">
                                            {{ str_replace('_', ' ', $perm->name) }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="save-role-btn">Save Role</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentRoleId = null;
    const systemRoles = ['super_admin', 'admin', 'finance_team', 'visa_team', 'ticketing_team'];

    // Load role permissions
    $('.role-item').on('click', function(e) {
        e.preventDefault();
        $('.role-item').removeClass('active');
        $(this).addClass('active');

        const roleId = $(this).data('role-id');
        const roleName = $(this).data('role-name');
        currentRoleId = roleId;

        $('#current-role-name').text(ucwords(roleName.replace(/_/g, ' ')));

        // Show/hide action buttons based on if it's a system role
        if (systemRoles.includes(roleName)) {
            $('#role-actions').hide();
        } else {
            $('#role-actions').show();
        }

        // Load permissions
        loadRolePermissions(roleId);
    });

    function loadRolePermissions(roleId) {
        $.ajax({
            url: '{{ url("api/roles") }}/' + roleId,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            success: function(response) {
                let html = '<div class="row">';

                const grouped = {};
                response.permissions.forEach(perm => {
                    const category = perm.name.split('_')[0];
                    if (!grouped[category]) grouped[category] = [];
                    grouped[category].push(perm.name);
                });

                for (let category in grouped) {
                    html += '<div class="col-md-4"><h6><strong>' + ucfirst(category) + '</strong></h6><ul class="list-unstyled text-sm">';
                    grouped[category].forEach(perm => {
                        html += '<li><i class="fas fa-check text-success"></i> ' + perm.replace(/_/g, ' ') + '</li>';
                    });
                    html += '</ul></div>';
                }

                html += '</div>';
                $('#permissions-list').html(html);
            },
            error: function() {
                $('#permissions-list').html('<p class="text-danger">Error loading permissions.</p>');
            }
        });
    }

    // Add role button
    $('#add-role-btn').on('click', function() {
        $('#roleModalTitle').text('Add New Role');
        $('#role-id').val('');
        $('#role-name').val('').prop('disabled', false);
        $('.permission-checkbox').prop('checked', false);
        $('#roleModal').modal('show');
    });

    // Edit role button
    $('#edit-role-btn').on('click', function() {
        if (!currentRoleId) return;

        $.ajax({
            url: '{{ url("api/roles") }}/' + currentRoleId,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            success: function(response) {
                $('#roleModalTitle').text('Edit Role');
                $('#role-id').val(response.role.id);
                $('#role-name').val(response.role.name).prop('disabled', true);

                $('.permission-checkbox').prop('checked', false);
                response.permissions.forEach(perm => {
                    $('.permission-checkbox[value="' + perm.name + '"]').prop('checked', true);
                });

                $('#roleModal').modal('show');
            }
        });
    });

    // Save role
    $('#save-role-btn').on('click', function() {
        const roleId = $('#role-id').val();
        const roleName = $('#role-name').val().trim();
        const permissions = [];

        $('.permission-checkbox:checked').each(function() {
            permissions.push($(this).val());
        });

        if (!roleName) {
            alert('Please enter a role name!');
            return;
        }

        const url = roleId ? '{{ url("api/roles") }}/' + roleId : '{{ url("api/roles") }}';
        const method = roleId ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: JSON.stringify({
                name: roleName,
                permissions: permissions
            }),
            success: function(response) {
                $('#roleModal').modal('hide');
                alert(response.message);
                location.reload();
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    alert(xhr.responseJSON.message);
                } else {
                    alert('Error saving role!');
                }
            }
        });
    });

    // Delete role
    $('#delete-role-btn').on('click', function() {
        if (!currentRoleId) return;

        if (!confirm('Are you sure you want to delete this role?')) return;

        $.ajax({
            url: '{{ url("api/roles") }}/' + currentRoleId,
            type: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                alert(response.message);
                location.reload();
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    alert(xhr.responseJSON.message);
                } else {
                    alert('Error deleting role!');
                }
            }
        });
    });

    // Helper functions
    function ucfirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function ucwords(str) {
        return str.replace(/\b\w/g, l => l.toUpperCase());
    }

    // Auto-load first role
    if ($('.role-item').length > 0) {
        $('.role-item').first().click();
    }
});
</script>
@endpush

