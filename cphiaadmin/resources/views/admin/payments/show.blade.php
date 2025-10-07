@extends('layouts.admin')

@section('title', 'Payment Details')
@section('page-title', 'Payment Details #' . $payment->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Payments</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
    <div class="row">
        <!-- Payment Information -->
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-credit-card"></i> Payment Information</h3>
                    <div class="card-tools">
                        @can('update_payment_status')
                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#statusModal">
                            <i class="fas fa-edit"></i> Update Status
                        </button>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th width="40%">Payment ID:</th>
                            <td><strong>#{{ $payment->id }}</strong></td>
                        </tr>
                        <tr>
                            <th>Transaction UUID:</th>
                            <td><code>{{ $payment->transaction_uuid ?? 'N/A' }}</code></td>
                        </tr>
                        <tr>
                            <th>Payment Reference:</th>
                            <td><code>{{ $payment->payment_reference ?? 'N/A' }}</code></td>
                        </tr>
                        <tr>
                            <th>Amount:</th>
                            <td><strong class="text-success">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Payment Status:</th>
                            <td>
                                @if($payment->payment_status == 'completed')
                                    <span class="badge badge-success badge-lg">Completed</span>
                                @elseif($payment->payment_status == 'pending')
                                    <span class="badge badge-warning badge-lg">Pending</span>
                                @elseif($payment->payment_status == 'failed')
                                    <span class="badge badge-danger badge-lg">Failed</span>
                                @else
                                    <span class="badge badge-secondary badge-lg">{{ ucfirst($payment->payment_status) }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Payment Method:</th>
                            <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Payment Date:</th>
                            <td>
                                @if($payment->payment_date)
                                    {{ $payment->payment_date->format('M d, Y H:i A') }}
                                @else
                                    <span class="text-muted">Not completed yet</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Created Date:</th>
                            <td>{{ $payment->created_at->format('M d, Y H:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $payment->updated_at->format('M d, Y H:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Registration Information -->
        <div class="col-md-6">
            @if($payment->registration)
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt"></i> Related Registration</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.registrations.show', $payment->registration->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> View Registration
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th width="40%">Registration ID:</th>
                            <td><a href="{{ route('admin.registrations.show', $payment->registration->id) }}">#{{ $payment->registration->id }}</a></td>
                        </tr>
                        <tr>
                            <th>Registration Type:</th>
                            <td><span class="badge badge-info">{{ ucfirst($payment->registration->registration_type) }}</span></td>
                        </tr>
                        <tr>
                            <th>Package:</th>
                            <td>{{ $payment->registration->package->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Total Amount:</th>
                            <td><strong>{{ $payment->registration->currency }} {{ number_format($payment->registration->total_amount, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Registration Status:</th>
                            <td>
                                @if($payment->registration->status == 'completed')
                                    <span class="badge badge-success">Completed</span>
                                @elseif($payment->registration->status == 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($payment->registration->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- User Information -->
            @if($payment->registration->user)
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> Customer Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th width="40%">Name:</th>
                            <td>{{ $payment->registration->user->first_name }} {{ $payment->registration->user->last_name }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><a href="mailto:{{ $payment->registration->user->email }}">{{ $payment->registration->user->email }}</a></td>
                        </tr>
                        @if($payment->registration->user->phone)
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $payment->registration->user->phone }}</td>
                        </tr>
                        @endif
                        @if($payment->registration->user->organization)
                        <tr>
                            <th>Organization:</th>
                            <td>{{ $payment->registration->user->organization }}</td>
                        </tr>
                        @endif
                        @if($payment->registration->user->country)
                        <tr>
                            <th>Country:</th>
                            <td>{{ $payment->registration->user->country }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
            @endif
            @else
            <div class="card card-warning">
                <div class="card-body">
                    <p class="text-muted"><i class="fas fa-exclamation-triangle"></i> No registration information available for this payment.</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Participants (if group registration) -->
    @if($payment->registration && $payment->registration->participants && $payment->registration->participants->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users"></i> Group Participants ({{ $payment->registration->participants->count() }})</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Nationality</th>
                                <th>Organization</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payment->registration->participants as $index => $participant)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $participant->first_name }} {{ $participant->last_name }}</td>
                                <td>{{ $participant->email ?? 'N/A' }}</td>
                                <td>{{ $participant->nationality ?? 'N/A' }}</td>
                                <td>{{ $participant->organization ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Actions -->
    <div class="row">
        <div class="col-12">
            <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <button class="btn btn-success" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Update Status Modal -->
    @can('update_payment_status')
    <div class="modal fade" id="statusModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title">Update Payment Status</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="statusForm">
                        <div class="form-group">
                            <label>Current Status:</label>
                            <p><span class="badge badge-{{ $payment->payment_status == 'completed' ? 'success' : ($payment->payment_status == 'failed' ? 'danger' : 'warning') }}">{{ ucfirst($payment->payment_status) }}</span></p>
                        </div>
                        <div class="form-group">
                            <label>New Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="payment-status" required>
                                <option value="pending" {{ $payment->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ $payment->payment_status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="failed" {{ $payment->payment_status == 'failed' ? 'selected' : '' }}>Failed</option>
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
    @endcan
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Save status
    $('#save-status').click(function() {
        const status = $('#payment-status').val();

        $.ajax({
            url: '{{ url("api/payments/" . $payment->id . "/status") }}',
            type: 'PUT',
            headers: {
                'Authorization': 'Bearer {{ session("api_token") }}',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: { payment_status: status },
            success: function(response) {
                $('#statusModal').modal('hide');
                alert('Payment status updated successfully!');
                location.reload();
            },
            error: function() {
                alert('Error updating payment status!');
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
    @media print {
        .main-sidebar, .main-header, .content-header, .btn, .breadcrumb, .modal {
            display: none !important;
        }
        .content-wrapper {
            margin: 0 !important;
        }
    }
    .badge-lg {
        font-size: 1.1em;
        padding: 0.5em 0.8em;
    }
</style>
@endpush
