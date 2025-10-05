@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'System Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="settings-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="roles-tab" data-toggle="pill" href="#roles" role="tab">
                                <i class="fas fa-user-tag"></i> Roles & Permissions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="general-tab" data-toggle="pill" href="#general" role="tab">
                                <i class="fas fa-cog"></i> General Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="email-tab" data-toggle="pill" href="#email" role="tab">
                                <i class="fas fa-envelope"></i> Email Settings
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="settings-tabContent">
                        <!-- Roles & Permissions Tab -->
                        <div class="tab-pane fade show active" id="roles" role="tabpanel">
                            <h4>Roles & Permissions Management</h4>
                            <p class="text-muted">Manage system roles and their associated permissions</p>

                            <div class="row mt-4">
                                <!-- Roles List -->
                                <div class="col-md-5">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">System Roles</h5>
                                        </div>
                                        <div class="card-body p-0">
                                            <div id="roles-list" class="list-group list-group-flush">
                                                <!-- Loaded via AJAX -->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Role Permissions -->
                                <div class="col-md-7">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0" id="selected-role-title">Select a role to view permissions</h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="role-permissions-container">
                                                <p class="text-muted text-center py-5">
                                                    <i class="fas fa-arrow-left fa-2x mb-3"></i><br>
                                                    Select a role from the left to view and manage its permissions
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- All Permissions Reference -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">All System Permissions ({{ $totalPermissions ?? 0 }})</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <h6>Dashboard</h6>
                                            <ul class="list-unstyled small" id="perm-dashboard"></ul>
                                        </div>
                                        <div class="col-md-3">
                                            <h6>Registrations</h6>
                                            <ul class="list-unstyled small" id="perm-registrations"></ul>
                                        </div>
                                        <div class="col-md-3">
                                            <h6>Payments</h6>
                                            <ul class="list-unstyled small" id="perm-payments"></ul>
                                        </div>
                                        <div class="col-md-3">
                                            <h6>Users</h6>
                                            <ul class="list-unstyled small" id="perm-users"></ul>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-3">
                                            <h6>Admin Users</h6>
                                            <ul class="list-unstyled small" id="perm-admins"></ul>
                                        </div>
                                        <div class="col-md-3">
                                            <h6>Reports</h6>
                                            <ul class="list-unstyled small" id="perm-reports"></ul>
                                        </div>
                                        <div class="col-md-3">
                                            <h6>Settings</h6>
                                            <ul class="list-unstyled small" id="perm-settings"></ul>
                                        </div>
                                        <div class="col-md-3">
                                            <h6>Other</h6>
                                            <ul class="list-unstyled small" id="perm-other"></ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- General Settings Tab -->
                        <div class="tab-pane fade" id="general" role="tabpanel">
                            <h4>General System Settings</h4>
                            <p class="text-muted">Configure general application settings</p>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Application Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <tr>
                                                    <th width="40%">Application Name:</th>
                                                    <td>{{ config('app.name') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Environment:</th>
                                                    <td><span class="badge badge-{{ config('app.env') === 'production' ? 'success' : 'warning' }}">{{ config('app.env') }}</span></td>
                                                </tr>
                                                <tr>
                                                    <th>Debug Mode:</th>
                                                    <td><span class="badge badge-{{ config('app.debug') ? 'danger' : 'success' }}">{{ config('app.debug') ? 'ON' : 'OFF' }}</span></td>
                                                </tr>
                                                <tr>
                                                    <th>Timezone:</th>
                                                    <td>{{ config('app.timezone') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Laravel Version:</th>
                                                    <td>{{ app()->version() }}</td>
                                                </tr>
                                                <tr>
                                                    <th>PHP Version:</th>
                                                    <td>{{ phpversion() }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Database Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <tr>
                                                    <th width="40%">Connection:</th>
                                                    <td>{{ config('database.default') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Database:</th>
                                                    <td>{{ config('database.connections.mysql.database') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Host:</th>
                                                    <td>{{ config('database.connections.mysql.host') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Port:</th>
                                                    <td>{{ config('database.connections.mysql.port') }}</td>
                                                </tr>
                                            </table>

                                            <div class="mt-3">
                                                <h6>Statistics:</h6>
                                                <ul class="list-unstyled">
                                                    <li><i class="fas fa-users text-info"></i> Total Users: <strong id="stat-users">Loading...</strong></li>
                                                    <li><i class="fas fa-user-plus text-success"></i> Registrations: <strong id="stat-registrations">Loading...</strong></li>
                                                    <li><i class="fas fa-credit-card text-warning"></i> Payments: <strong id="stat-payments">Loading...</strong></li>
                                                    <li><i class="fas fa-user-shield text-danger"></i> Admin Users: <strong id="stat-admins">Loading...</strong></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">System Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <button class="btn btn-info btn-block" onclick="clearCache()">
                                                <i class="fas fa-sync"></i> Clear Cache
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-warning btn-block" onclick="clearLogs()">
                                                <i class="fas fa-trash"></i> Clear Logs
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-success btn-block" onclick="optimizeSystem()">
                                                <i class="fas fa-rocket"></i> Optimize System
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-secondary btn-block" onclick="viewLogs()">
                                                <i class="fas fa-file-alt"></i> View Logs
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Email Settings Tab -->
                        <div class="tab-pane fade" id="email" role="tabpanel">
                            <h4>Email Configuration</h4>
                            <p class="text-muted">Configure email settings for the system</p>

                            <div class="card mt-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Current Email Configuration</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="30%">Mail Driver:</th>
                                            <td>{{ config('mail.default') }}</td>
                                        </tr>
                                        <tr>
                                            <th>From Address:</th>
                                            <td>{{ config('mail.from.address') }}</td>
                                        </tr>
                                        <tr>
                                            <th>From Name:</th>
                                            <td>{{ config('mail.from.name') }}</td>
                                        </tr>
                                    </table>

                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Note:</strong> Email configuration is managed through the .env file and requires server restart to apply changes.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    loadRoles();
    loadAllPermissions();
    loadStats();
});

function loadRoles() {
    $.ajax({
        url: '{{ url("api/settings/roles") }}',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer {{ session("api_token") }}',
            'Accept': 'application/json'
        },
        success: function(response) {
            let html = '';
            response.roles.forEach(function(role) {
                html += `
                    <a href="#" class="list-group-item list-group-item-action role-item" data-role="${role.name}">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${role.display_name}</h6>
                            <small class="badge badge-primary">${role.permissions_count} perms</small>
                        </div>
                        <p class="mb-1 small text-muted">${role.description || 'No description'}</p>
                    </a>
                `;
            });
            $('#roles-list').html(html);

            // Bind click events
            $('.role-item').click(function(e) {
                e.preventDefault();
                $('.role-item').removeClass('active');
                $(this).addClass('active');
                loadRolePermissions($(this).data('role'));
            });
        }
    });
}

function loadRolePermissions(roleName) {
    $('#selected-role-title').text('Permissions for: ' + roleName.replace('_', ' ').toUpperCase());

    $.ajax({
        url: '{{ url("api/settings/roles") }}/' + roleName + '/permissions',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer {{ session("api_token") }}',
            'Accept': 'application/json'
        },
        success: function(response) {
            let html = '<div class="row">';

            // Group permissions by category
            const grouped = {};
            response.permissions.forEach(perm => {
                const category = perm.category || 'Other';
                if (!grouped[category]) grouped[category] = [];
                grouped[category].push(perm);
            });

            // Display grouped permissions
            Object.keys(grouped).forEach(category => {
                html += `
                    <div class="col-md-6 mb-3">
                        <h6 class="text-primary">${category}</h6>
                        <ul class="list-unstyled">
                `;
                grouped[category].forEach(perm => {
                    html += `
                        <li>
                            <i class="fas fa-check-circle text-success"></i>
                            ${perm.display_name || perm.name}
                        </li>
                    `;
                });
                html += '</ul></div>';
            });

            html += '</div>';
            $('#role-permissions-container').html(html);
        }
    });
}

function loadAllPermissions() {
    $.ajax({
        url: '{{ url("api/settings/permissions") }}',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer {{ session("api_token") }}',
            'Accept': 'application/json'
        },
        success: function(response) {
            const categories = {
                dashboard: [], registrations: [], payments: [], users: [],
                admins: [], reports: [], settings: [], other: []
            };

            response.permissions.forEach(perm => {
                const name = perm.name.toLowerCase();
                if (name.includes('dashboard')) categories.dashboard.push(perm.name);
                else if (name.includes('registration')) categories.registrations.push(perm.name);
                else if (name.includes('payment')) categories.payments.push(perm.name);
                else if (name.includes('user') && !name.includes('admin')) categories.users.push(perm.name);
                else if (name.includes('admin')) categories.admins.push(perm.name);
                else if (name.includes('report')) categories.reports.push(perm.name);
                else if (name.includes('setting')) categories.settings.push(perm.name);
                else categories.other.push(perm.name);
            });

            Object.keys(categories).forEach(cat => {
                const html = categories[cat].map(p => `<li><small>${p}</small></li>`).join('');
                $(`#perm-${cat}`).html(html || '<li><small class="text-muted">None</small></li>');
            });
        }
    });
}

function loadStats() {
    // Load database statistics
    $.ajax({
        url: '{{ url("api/settings/stats") }}',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer {{ session("api_token") }}',
            'Accept': 'application/json'
        },
        success: function(response) {
            $('#stat-users').text(response.users || 0);
            $('#stat-registrations').text(response.registrations || 0);
            $('#stat-payments').text(response.payments || 0);
            $('#stat-admins').text(response.admins || 0);
        },
        error: function() {
            $('#stat-users, #stat-registrations, #stat-payments, #stat-admins').text('N/A');
        }
    });
}

function clearCache() {
    if (confirm('Are you sure you want to clear the system cache?')) {
        alert('Cache clearing functionality - to be implemented');
    }
}

function clearLogs() {
    if (confirm('Are you sure you want to clear all log files?')) {
        alert('Log clearing functionality - to be implemented');
    }
}

function optimizeSystem() {
    if (confirm('Optimize the system? This will clear caches and optimize routes.')) {
        alert('System optimization - to be implemented');
    }
}

function viewLogs() {
    alert('Log viewer - to be implemented');
}
</script>
@endpush
