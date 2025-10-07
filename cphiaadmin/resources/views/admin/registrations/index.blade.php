@extends('layouts.admin')

@section('title', 'Registrations')
@section('page-title', 'Registrations Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Registrations</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Registrations</h3>
            <div class="card-tools">
                @can('export_registrations')
                <button type="button" class="btn btn-success btn-sm" id="export-btn">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Registration Type</label>
                    <select class="form-control form-control-sm" id="filter-type">
                        <option value="">All Types</option>
                        <option value="individual">Individual</option>
                        <option value="side_event">Side Event</option>
                        <option value="exhibition">Exhibition</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Status</label>
                    <select class="form-control form-control-sm" id="filter-status">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Payment Status</label>
                    <select class="form-control form-control-sm" id="filter-payment-status">
                        <option value="">All Payments</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Date From</label>
                    <input type="date" class="form-control form-control-sm" id="filter-date-from">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Date To</label>
                    <input type="date" class="form-control form-control-sm" id="filter-date-to">
                </div>
                <div class="col-md-3">
                    <label>Search</label>
                    <input type="text" class="form-control form-control-sm" id="filter-search" placeholder="Search by name, email...">
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm btn-block" id="apply-filters">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <div>
                        <button type="button" class="btn btn-secondary btn-sm btn-block" id="reset-filters">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="registrations-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Package</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Participants</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data loaded via AJAX -->
                    </tbody>
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
            registration_type: $('#filter-type').val(),
            status: $('#filter-status').val(),
            payment_status: $('#filter-payment-status').val(),
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val(),
            search: $('#filter-search').val(),
        };

        table = $('#registrations-table').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '{{ url("api/registrations") }}',
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
                { data: 'user_name' },
                { data: 'email' },
                { data: 'registration_type' },
                { data: 'package' },
                {
                    data: null,
                    render: function(data) {
                        return data.currency + ' ' + data.total_amount;
                    }
                },
                {
                    data: 'status',
                    render: function(data) {
                        const badges = {
                            'pending': 'warning',
                            'completed': 'success',
                            'cancelled': 'danger'
                        };
                        return '<span class="badge badge-' + (badges[data] || 'secondary') + '">' +
                               data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
                    }
                },
                {
                    data: 'payment_status',
                    render: function(data) {
                        const badges = {
                            'pending': 'warning',
                            'completed': 'success',
                            'failed': 'danger'
                        };
                        return '<span class="badge badge-' + (badges[data] || 'secondary') + '">' +
                               data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
                    }
                },
                { data: 'participants_count' },
                { data: 'created_at' },
                {
                    data: null,
                    orderable: false,
                    render: function(data) {
                        return '<a href="{{ url("admin/registrations") }}/' + data.id + '" class="btn btn-sm btn-info">' +
                               '<i class="fas fa-eye"></i> View</a>';
                    }
                }
            ],
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: {
                emptyTable: "No registrations found",
                processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
            }
        });
    }

    // Initial load
    loadTable();

    // Apply filters
    $('#apply-filters').click(function() {
        loadTable();
    });

    // Reset filters
    $('#reset-filters').click(function() {
        $('#filter-type').val('');
        $('#filter-status').val('');
        $('#filter-payment-status').val('');
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
        $('#filter-search').val('');
        loadTable();
    });

    // Export functionality
    $('#export-btn').click(function() {
        const filters = {
            registration_type: $('#filter-type').val(),
            status: $('#filter-status').val(),
            payment_status: $('#filter-payment-status').val(),
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val(),
        };

        const queryString = $.param(filters);
        window.location.href = '{{ url("api/registrations/export") }}?' + queryString;
    });
});
</script>
@endpush

