@extends('layouts.admin')

@section('title', 'Registration Details')
@section('page-title', 'Registration Details #' . $registration->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.registrations.index') }}">Registrations</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
    <div class="row">
        <!-- Registration Information -->
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Registration Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th width="40%">Registration ID:</th>
                            <td><strong>#{{ $registration->id }}</strong></td>
                        </tr>
                        <tr>
                            <th>Registration Type:</th>
                            <td>
                                <span class="badge badge-info">{{ ucfirst($registration->registration_type) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Package:</th>
                            <td>{{ $registration->package->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Total Amount:</th>
                            <td><strong>{{ $registration->currency }} {{ number_format($registration->total_amount, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($registration->status == 'completed')
                                    <span class="badge badge-success">Completed</span>
                                @elseif($registration->status == 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($registration->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Payment Status:</th>
                            <td>
                                @if($registration->payment_status == 'completed')
                                    <span class="badge badge-success">Paid</span>
                                @elseif($registration->payment_status == 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($registration->payment_status == 'failed')
                                    <span class="badge badge-danger">Failed</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($registration->payment_status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @if($registration->payment_reference)
                        <tr>
                            <th>Payment Reference:</th>
                            <td><code>{{ $registration->payment_reference }}</code></td>
                        </tr>
                        @endif
                        @if($registration->payment_transaction_id)
                        <tr>
                            <th>Transaction ID:</th>
                            <td><code>{{ $registration->payment_transaction_id }}</code></td>
                        </tr>
                        @endif
                        <tr>
                            <th>Created Date:</th>
                            <td>{{ $registration->created_at->format('M d, Y H:i A') }}</td>
                        </tr>
                        @if($registration->payment_completed_at)
                        <tr>
                            <th>Payment Completed:</th>
                            <td>{{ $registration->payment_completed_at->format('M d, Y H:i A') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            @if($registration->exhibition_description)
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-store"></i> Exhibition Details</h3>
                </div>
                <div class="card-body">
                    <p>{{ $registration->exhibition_description }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- User Information -->
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> Registrant Information</h3>
                </div>
                <div class="card-body">
                    @if($registration->user)
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th width="40%">Name:</th>
                            <td>{{ $registration->user->title ?? '' }} {{ $registration->user->first_name }} {{ $registration->user->last_name }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><a href="mailto:{{ $registration->user->email }}">{{ $registration->user->email }}</a></td>
                        </tr>
                        @if($registration->user->phone)
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $registration->user->phone }}</td>
                        </tr>
                        @endif
                        @if($registration->user->organization)
                        <tr>
                            <th>Organization:</th>
                            <td>{{ $registration->user->organization }}</td>
                        </tr>
                        @endif
                        @if($registration->user->position)
                        <tr>
                            <th>Position:</th>
                            <td>{{ $registration->user->position }}</td>
                        </tr>
                        @endif
                        @if($registration->user->country)
                        <tr>
                            <th>Country:</th>
                            <td>{{ $registration->user->country }}</td>
                        </tr>
                        @endif
                        @if($registration->user->nationality)
                        <tr>
                            <th>Nationality:</th>
                            <td>{{ $registration->user->nationality }}</td>
                        </tr>
                        @endif
                        @if($registration->user->delegate_category)
                        <tr>
                            <th>Delegate Category:</th>
                            <td><span class="badge badge-primary">{{ $registration->user->delegate_category }}</span></td>
                        </tr>
                        @endif
                        <tr>
                            <th>Requires Visa:</th>
                            <td>
                                @if($registration->user->requires_visa)
                                    <span class="badge badge-warning">Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                    @else
                    <p class="text-muted">No user information available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Participants (for group registrations) -->
    @if($registration->participants && $registration->participants->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users"></i> Group Participants ({{ $registration->participants->count() }})</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Nationality</th>
                                <th>Passport Number</th>
                                <th>Organization</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($registration->participants as $index => $participant)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $participant->title ?? '' }} {{ $participant->first_name }} {{ $participant->last_name }}</td>
                                <td>{{ $participant->email ?? 'N/A' }}</td>
                                <td>{{ $participant->nationality ?? 'N/A' }}</td>
                                <td>{{ $participant->passport_number ?? 'N/A' }}</td>
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

    <!-- Payment History -->
    @if($registration->payments && $registration->payments->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-credit-card"></i> Payment History ({{ $registration->payments->count() }})</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transaction ID</th>
                                <th>Reference</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($registration->payments as $payment)
                            <tr>
                                <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                <td><code>{{ $payment->transaction_uuid ?? 'N/A' }}</code></td>
                                <td>{{ $payment->payment_reference ?? 'N/A' }}</td>
                                <td><strong>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</strong></td>
                                <td>
                                    @if($payment->payment_status == 'completed')
                                        <span class="badge badge-success">Completed</span>
                                    @elseif($payment->payment_status == 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($payment->payment_status == 'failed')
                                        <span class="badge badge-danger">Failed</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($payment->payment_status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $payment->payment_method ?? 'N/A' }}</td>
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
            <a href="{{ route('admin.registrations.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            @can('export_registrations')
            <button class="btn btn-success" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
            @endcan
        </div>
    </div>
@endsection

@push('styles')
<style>
    @media print {
        .main-sidebar, .main-header, .content-header, .btn, .breadcrumb {
            display: none !important;
        }
        .content-wrapper {
            margin: 0 !important;
        }
    }
</style>
@endpush
