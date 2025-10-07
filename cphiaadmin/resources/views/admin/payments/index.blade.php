@extends('layouts.admin')

@section('title', 'Payments')
@section('page-title', 'Payments Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Payments</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Payments</h3>
            <div class="card-tools">
                @can('export_payments')
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
                    <label>Payment Status</label>
                    <select class="form-control form-control-sm" id="filter-status">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Date From</label>
                    <input type="date" class="form-control form-control-sm" id="filter-date-from">
                </div>
                <div class="col-md-3">
                    <label>Date To</label>
                    <input type="date" class="form-control form-control-sm" id="filter-date-to">
                </div>
                <div class="col-md-3">
                    <label>Search</label>
                    <input type="text" class="form-control form-control-sm" id="filter-search" placeholder="Search...">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary btn-sm btn-block" id="apply-filters">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-secondary btn-sm btn-block" id="reset-filters">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="payments-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Transaction ID</th>
                            <th>Reference</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Method</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="statusModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Update Payment Status</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="statusForm">
                        <input type="hidden" id="payment-id">
                        <div class="form-group">
                            <label>Payment Status</label>
                            <select class="form-control" id="payment-status" required>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="save-status">Save Changes</button>
                </div>
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
            payment_status: $('#filter-status').val(),
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val(),
            search: $('#filter-search').val(),
        };

        table = $('#payments-table').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '{{ url("api/payments") }}',
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
                { data: 'transaction_uuid' },
                { data: 'payment_reference' },
                {
                    data: null,
                    render: function(data) {
                        return data.currency + ' ' + data.amount;
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
                { data: 'payment_method' },
                { data: 'payment_date' },
                {
                    data: null,
                    orderable: false,
                    render: function(data) {
                        let html = '<div class="btn-group btn-group-sm">';
                        html += '<a href="{{ url("admin/payments") }}/' + data.id + '" class="btn btn-info"><i class="fas fa-eye"></i></a>';
                        @can('update_payment_status')
                        html += '<button class="btn btn-primary update-status-btn" data-id="' + data.id + '" data-status="' + data.payment_status + '"><i class="fas fa-edit"></i></button>';
                        @endcan
                        html += '</div>';
                        return html;
                    }
                }
            ],
            order: [[0, 'desc']],
            pageLength: 10,
            language: {
                emptyTable: "No payments found"
            }
        });

        // Bind update status button
        $('#payments-table').on('click', '.update-status-btn', function() {
            const id = $(this).data('id');
            const status = $(this).data('status');
            $('#payment-id').val(id);
            $('#payment-status').val(status);
            $('#statusModal').modal('show');
        });
    }

    loadTable();

    $('#apply-filters').click(function() {
        loadTable();
    });

    $('#reset-filters').click(function() {
        $('#filter-status, #filter-date-from, #filter-date-to, #filter-search').val('');
        loadTable();
    });

    $('#export-btn').click(function() {
        const filters = {
            payment_status: $('#filter-status').val(),
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val(),
        };
        window.location.href = '{{ url("api/payments/export") }}?' + $.param(filters);
    });

    // Save status
    $('#save-status').click(function() {
        const id = $('#payment-id').val();
        const status = $('#payment-status').val();

        $.ajax({
            url: '{{ url("api/payments") }}/' + id + '/status',
            type: 'PUT',
            headers: {
                'Authorization': 'Bearer ' + '{{ session("api_token") }}',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: { payment_status: status },
            success: function(response) {
                $('#statusModal').modal('hide');
                loadTable();
                alert('Payment status updated successfully!');
            },
            error: function() {
                alert('Error updating payment status!');
            }
        });
    });
});
</script>
@endpush

