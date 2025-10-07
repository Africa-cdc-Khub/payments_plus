@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <div class="row">
        <!-- Total Registrations -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['total_registrations']) }}</h3>
                    <p>Total Registrations</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                @can('view_registrations')
                <a href="{{ route('admin.registrations.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
                @else
                <div class="small-box-footer">&nbsp;</div>
                @endcan
            </div>
        </div>

        <!-- Individual Registrations -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats['individual_registrations']) }}</h3>
                    <p>Individual</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="small-box-footer">&nbsp;</div>
            </div>
        </div>

        <!-- Side Event Registrations -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($stats['side_event_registrations']) }}</h3>
                    <p>Side Events</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="small-box-footer">&nbsp;</div>
            </div>
        </div>

        <!-- Exhibition Registrations -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($stats['exhibition_registrations']) }}</h3>
                    <p>Exhibitions</p>
                </div>
                <div class="icon">
                    <i class="fas fa-store"></i>
                </div>
                <div class="small-box-footer">&nbsp;</div>
            </div>
        </div>
    </div>

    <!-- Payment Stats (Finance Team, Admin, Super Admin) -->
    @if(auth()->user()->hasAnyRole(['super_admin', 'admin', 'finance_team']))
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Revenue</span>
                    <span class="info-box-number">${{ number_format($stats['total_revenue'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending Payments</span>
                    <span class="info-box-number">{{ number_format($stats['pending_payments']) }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Completed</span>
                    <span class="info-box-number">{{ number_format($stats['completed_payments']) }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Failed</span>
                    <span class="info-box-number">{{ number_format($stats['failed_payments']) }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Nationality & Other Stats -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Participants</span>
                    <span class="info-box-number">{{ number_format($stats['total_users']) }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-globe-africa"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">African Nationals</span>
                    <span class="info-box-number">{{ number_format($stats['african_nationals']) }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-globe"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Non-African</span>
                    <span class="info-box-number">{{ number_format($stats['non_african_nationals']) }}</span>
                </div>
            </div>
        </div>

        @if(auth()->user()->hasAnyRole(['super_admin', 'admin', 'visa_team']))
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-passport"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Visa Required</span>
                    <span class="info-box-number">{{ number_format($stats['total_visa_required']) }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Registration Trend Chart -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-1"></i>
                        Registration Trend (Last 7 Days)
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="registrationTrendChart" style="min-height: 250px; max-height: 250px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Payment Status Chart (Finance Only) -->
        @if(auth()->user()->hasAnyRole(['super_admin', 'admin', 'finance_team']))
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1"></i>
                        Payment Status Distribution
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="paymentStatusChart" style="min-height: 250px; max-height: 250px;"></canvas>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <!-- Recent Registrations -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Registrations</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentRegistrations as $registration)
                            <tr>
                                <td>{{ $registration->user->first_name ?? 'N/A' }} {{ $registration->user->last_name ?? '' }}</td>
                                <td><span class="badge badge-info">{{ ucfirst($registration->registration_type) }}</span></td>
                                <td>{{ $registration->created_at->format('M d, Y') }}</td>
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
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No recent registrations</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        @if(auth()->user()->hasAnyRole(['super_admin', 'admin', 'finance_team']))
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Payments</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments as $payment)
                            <tr>
                                <td>{{ $payment->payment_reference ?? $payment->transaction_uuid }}</td>
                                <td>${{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->created_at->format('M d, Y') }}</td>
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
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No recent payments</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    // Registration Trend Chart
    const registrationTrendCtx = document.getElementById('registrationTrendChart').getContext('2d');
    const registrationTrendData = @json($stats['registration_trend']);

    const registrationTrendChart = new Chart(registrationTrendCtx, {
        type: 'line',
        data: {
            labels: registrationTrendData.map(item => item.date),
            datasets: [{
                label: 'Registrations',
                data: registrationTrendData.map(item => item.count),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    @if(auth()->user()->hasAnyRole(['super_admin', 'admin', 'finance_team']))
    // Payment Status Chart
    const paymentStatusCtx = document.getElementById('paymentStatusChart').getContext('2d');
    const paymentStatusData = @json($stats['payment_by_status'] ?? []);

    const paymentStatusChart = new Chart(paymentStatusCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(paymentStatusData).map(status => status.charAt(0).toUpperCase() + status.slice(1)),
            datasets: [{
                data: Object.values(paymentStatusData),
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',   // Green for completed
                    'rgba(255, 193, 7, 0.8)',   // Yellow for pending
                    'rgba(220, 53, 69, 0.8)',   // Red for failed
                ],
                borderColor: [
                    'rgb(40, 167, 69)',
                    'rgb(255, 193, 7)',
                    'rgb(220, 53, 69)',
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
    @endif
</script>
@endpush

