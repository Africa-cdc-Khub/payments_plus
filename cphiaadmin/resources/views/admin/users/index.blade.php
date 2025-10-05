@extends('layouts.admin')

@section('title', 'Participants')
@section('page-title', 'Participants Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Participants</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Participants</h3>
            <div class="card-tools">
                @can('export_users')
                <button type="button" class="btn btn-success btn-sm" id="export-btn">
                    <i class="fas fa-file-excel"></i> Export
                </button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Attendance Status</label>
                    <select class="form-control form-control-sm" id="filter-attendance">
                        <option value="">All</option>
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Requires Visa</label>
                    <select class="form-control form-control-sm" id="filter-visa">
                        <option value="">All</option>
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Search</label>
                    <input type="text" class="form-control form-control-sm" id="filter-search" placeholder="Search...">
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-primary btn-sm btn-block" id="apply-filters">
                        <i class="fas fa-search"></i> Apply
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Country</th>
                            <th>Organization</th>
                            <th>Visa</th>
                            <th>Attendance</th>
                            <th>Registrations</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let table = null;

    function loadTable() {
        if (table) {
            table.destroy();
        }

        const filters = {
            attendance_status: $('#filter-attendance').val(),
            requires_visa: $('#filter-visa').val(),
            search: $('#filter-search').val(),
        };

        table = $('#users-table').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '{{ url("api/users") }}',
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + '{{ session("api_token") }}',
                    'Accept': 'application/json'
                },
                data: filters,
                dataSrc: 'data'
            },
            columns: [
                { data: 'id' },
                { data: 'name' },
                { data: 'email' },
                { data: 'country' },
                { data: 'organization' },
                {
                    data: 'requires_visa',
                    render: function(data) {
                        return data === 'Yes' ? '<span class="badge badge-warning">Yes</span>' :
                               '<span class="badge badge-secondary">No</span>';
                    }
                },
                {
                    data: 'attendance_status',
                    render: function(data) {
                        const badges = {
                            'present': 'success',
                            'absent': 'danger',
                            'pending': 'warning'
                        };
                        return '<span class="badge badge-' + (badges[data] || 'secondary') + '">' +
                               data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
                    }
                },
                { data: 'registrations_count' },
                {
                    data: null,
                    orderable: false,
                    render: function(data) {
                        return '<a href="{{ url("admin/users") }}/' + data.id + '" class="btn btn-sm btn-info">' +
                               '<i class="fas fa-eye"></i></a>';
                    }
                }
            ],
            order: [[0, 'desc']],
            pageLength: 10
        });
    }

    loadTable();

    $('#apply-filters').click(function() {
        loadTable();
    });

    $('#export-btn').click(function() {
        const filters = {
            attendance_status: $('#filter-attendance').val(),
            requires_visa: $('#filter-visa').val(),
        };
        window.location.href = '{{ url("api/users/export") }}?' + $.param(filters);
    });
});
</script>
@endpush

