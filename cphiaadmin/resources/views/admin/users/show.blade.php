@extends('layouts.admin')

@section('title', 'Participant Details')
@section('page-title', 'Participant Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Participants</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
    <div class="row">
        <!-- Personal Information -->
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> Personal Information</h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="user-avatar">
                            <i class="fas fa-user-circle fa-5x text-primary"></i>
                        </div>
                        <h4 class="mt-2">{{ $user->title ?? '' }} {{ $user->first_name }} {{ $user->last_name }}</h4>
                        <p class="text-muted">{{ $user->delegate_category ?? 'Participant' }}</p>
                    </div>

                    <table class="table table-sm table-bordered">
                        <tr>
                            <th width="40%">User ID:</th>
                            <td><strong>#{{ $user->id }}</strong></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></td>
                        </tr>
                        @if($user->phone)
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $user->phone }}</td>
                        </tr>
                        @endif
                        @if($user->nationality)
                        <tr>
                            <th>Nationality:</th>
                            <td>{{ $user->nationality }}</td>
                        </tr>
                        @endif
                        @if($user->country)
                        <tr>
                            <th>Country of Residence:</th>
                            <td>{{ $user->country }}</td>
                        </tr>
                        @endif
                        @if($user->passport_number)
                        <tr>
                            <th>Passport Number:</th>
                            <td><code>{{ $user->passport_number }}</code></td>
                        </tr>
                        @endif
                        <tr>
                            <th>Member Since:</th>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Billing Address Information -->
            @if($user->address_line1 || $user->city || $user->state || $user->postal_code)
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Billing Address Information</h3>
                </div>
                <div class="card-body">
                    @if($user->address_line1)
                        <p class="mb-1">{{ $user->address_line1 }}</p>
                    @endif
                    @if($user->address_line2)
                        <p class="mb-1">{{ $user->address_line2 }}</p>
                    @endif
                    @if($user->city || $user->state || $user->postal_code)
                        <p class="mb-1">
                            {{ $user->city }}{{ $user->state ? ', ' . $user->state : '' }} {{ $user->postal_code }}
                        </p>
                    @endif
                    @if($user->country)
                        <p class="mb-0"><strong>{{ $user->country }}</strong></p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Professional Information -->
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-briefcase"></i> Professional Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered">
                        @if($user->organization)
                        <tr>
                            <th width="40%">Organization:</th>
                            <td>{{ $user->organization }}</td>
                        </tr>
                        @endif
                        @if($user->position)
                        <tr>
                            <th>Position:</th>
                            <td>{{ $user->position }}</td>
                        </tr>
                        @endif
                        @if($user->institution)
                        <tr>
                            <th>Institution:</th>
                            <td>{{ $user->institution }}</td>
                        </tr>
                        @endif
                        @if($user->delegate_category)
                        <tr>
                            <th>Delegate Category:</th>
                            <td><span class="badge badge-primary">{{ $user->delegate_category }}</span></td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Visa Information -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-passport"></i> Visa & Travel Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th width="40%">Requires Visa:</th>
                            <td>
                                @if($user->requires_visa)
                                    <span class="badge badge-warning">Yes</span>
                                @else
                                    <span class="badge badge-success">No</span>
                                @endif
                            </td>
                        </tr>
                        @if($user->passport_file)
                        <tr>
                            <th>Passport Document:</th>
                            <td>
                                <span class="badge badge-success">Uploaded</span>
                                <a href="#" class="btn btn-xs btn-primary ml-2">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </td>
                        </tr>
                        @endif
                        @if($user->student_id_file)
                        <tr>
                            <th>Student ID:</th>
                            <td>
                                <span class="badge badge-success">Uploaded</span>
                                <a href="#" class="btn btn-xs btn-primary ml-2">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </td>
                        </tr>
                        @endif
                        @if($user->airport_of_origin)
                        <tr>
                            <th>Airport of Origin:</th>
                            <td>{{ $user->airport_of_origin }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Attendance Status -->
            <div class="card card-{{ $user->attendance_status == 'present' ? 'success' : ($user->attendance_status == 'absent' ? 'danger' : 'secondary') }}">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-check-circle"></i> Attendance Status</h3>
                    @can('manage_ticketing_data')
                    <div class="card-tools">
                        <button class="btn btn-sm btn-light" data-toggle="modal" data-target="#attendanceModal">
                            <i class="fas fa-edit"></i> Update
                        </button>
                    </div>
                    @endcan
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th width="40%">Status:</th>
                            <td>
                                @if($user->attendance_status == 'present')
                                    <span class="badge badge-success badge-lg">Present</span>
                                @elseif($user->attendance_status == 'absent')
                                    <span class="badge badge-danger badge-lg">Absent</span>
                                @else
                                    <span class="badge badge-secondary badge-lg">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @if($user->attendance_verified_at)
                        <tr>
                            <th>Verified At:</th>
                            <td>{{ $user->attendance_verified_at->format('M d, Y H:i A') }}</td>
                        </tr>
                        @endif
                        @if($user->verified_by)
                        <tr>
                            <th>Verified By:</th>
                            <td>Admin #{{ $user->verified_by }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Registrations -->
    @if($user->registrations && $user->registrations->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Registration History ({{ $user->registrations->count() }})</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Registration ID</th>
                                <th>Type</th>
                                <th>Package</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->registrations as $registration)
                            <tr>
                                <td><a href="{{ route('admin.registrations.show', $registration->id) }}">#{{ $registration->id }}</a></td>
                                <td><span class="badge badge-info">{{ ucfirst($registration->registration_type) }}</span></td>
                                <td>{{ $registration->package->name ?? 'N/A' }}</td>
                                <td><strong>{{ $registration->currency }} {{ number_format($registration->total_amount, 2) }}</strong></td>
                                <td>
                                    @if($registration->status == 'completed')
                                        <span class="badge badge-success">Completed</span>
                                    @elseif($registration->status == 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($registration->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($registration->payment_status == 'completed')
                                        <span class="badge badge-success">Paid</span>
                                    @elseif($registration->payment_status == 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @else
                                        <span class="badge badge-danger">{{ ucfirst($registration->payment_status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $registration->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.registrations.show', $registration->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
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
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <button class="btn btn-success" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Attendance Update Modal -->
    @can('manage_ticketing_data')
    <div class="modal fade" id="attendanceModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title">Update Attendance Status</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="attendanceForm">
                        <div class="form-group">
                            <label>Current Status:</label>
                            <p>
                                <span class="badge badge-{{ $user->attendance_status == 'present' ? 'success' : ($user->attendance_status == 'absent' ? 'danger' : 'secondary') }}">
                                    {{ ucfirst($user->attendance_status ?? 'pending') }}
                                </span>
                            </p>
                        </div>
                        <div class="form-group">
                            <label>New Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="attendance-status" required>
                                <option value="pending" {{ $user->attendance_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="present" {{ $user->attendance_status == 'present' ? 'selected' : '' }}>Present</option>
                                <option value="absent" {{ $user->attendance_status == 'absent' ? 'selected' : '' }}>Absent</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="save-attendance">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
    @endcan
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Save attendance
    $('#save-attendance').click(function() {
        const status = $('#attendance-status').val();

        $.ajax({
            url: '{{ url("api/users/" . $user->id . "/attendance") }}',
            type: 'PUT',
            headers: {
                'Authorization': 'Bearer {{ session("api_token") }}',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: { attendance_status: status },
            success: function(response) {
                $('#attendanceModal').modal('hide');
                alert('Attendance status updated successfully!');
                location.reload();
            },
            error: function() {
                alert('Error updating attendance status!');
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
    .user-avatar {
        margin: 20px 0;
    }
</style>
@endpush
