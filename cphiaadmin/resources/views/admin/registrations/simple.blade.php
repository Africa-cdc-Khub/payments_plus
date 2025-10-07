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
            <h3 class="card-title"><i class="fas fa-list"></i> All Registrations</h3>
            <div class="card-tools">
                @can('export_registrations')
                <a href="{{ route('admin.reports.registrations', ['format' => 'excel']) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Registrant</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Package</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $registrations = \App\Models\Registration::with(['user', 'package'])
                                ->orderBy('created_at', 'desc')
                                ->get();
                        @endphp

                        @forelse($registrations as $registration)
                        <tr>
                            <td>{{ $registration->id }}</td>
                            <td>
                                @if($registration->user)
                                    {{ $registration->user->first_name }} {{ $registration->user->last_name }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $registration->user->email ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ ucfirst($registration->registration_type) }}
                                </span>
                            </td>
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
                                @elseif($registration->payment_status == 'failed')
                                    <span class="badge badge-danger">Failed</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($registration->payment_status ?? 'N/A') }}</span>
                                @endif
                            </td>
                            <td>{{ $registration->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.registrations.show', $registration->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">No registrations found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle"></i> Total: {{ $registrations->count() }} registrations
            </p>
        </div>
    </div>
@endsection

