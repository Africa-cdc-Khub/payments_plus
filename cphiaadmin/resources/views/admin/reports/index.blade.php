@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Reports & Analytics')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Generate Reports</h3>
                </div>
                <div class="card-body">
                    <p class="lead">Select a report type below to generate detailed analytics and insights.</p>

                    <div class="row">
                        <!-- Registrations Report -->
                        <div class="col-md-6 col-lg-4">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h4>Registrations Report</h4>
                                    <p>View all registration data with filters by type, status, and date range</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <a href="{{ route('admin.reports.registrations') }}" class="small-box-footer">
                                    Generate Report <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Financial Report -->
                        @can('view_finance_reports')
                        <div class="col-md-6 col-lg-4">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h4>Financial Report</h4>
                                    <p>Payment transactions, revenue analysis, and financial summaries</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <a href="{{ route('admin.reports.financial') }}" class="small-box-footer">
                                    Generate Report <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        @endcan

                        <!-- Visa Report -->
                        @can('view_visa_reports')
                        <div class="col-md-6 col-lg-4">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h4>Visa Report</h4>
                                    <p>Visa requirements, passport documents, and nationality breakdown</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-passport"></i>
                                </div>
                                <a href="{{ route('admin.reports.visa') }}" class="small-box-footer">
                                    Generate Report <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        @endcan

                        <!-- Attendance Report -->
                        @can('view_ticketing_reports')
                        <div class="col-md-6 col-lg-4">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h4>Attendance Report</h4>
                                    <p>Attendance tracking, presence status, and delegate categories</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <a href="{{ route('admin.reports.attendance') }}" class="small-box-footer">
                                    Generate Report <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        @endcan

                        <!-- Summary Report -->
                        <div class="col-md-6 col-lg-4">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h4>Summary Report</h4>
                                    <p>Quick overview of all key metrics and statistics</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <a href="{{ route('admin.reports.summary') }}" class="small-box-footer">
                                    Generate Report <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Report Features</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <i class="fas fa-filter fa-3x text-info mb-2"></i>
                            <h5>Advanced Filters</h5>
                            <p class="text-muted">Filter data by date range, status, type, and more</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                            <h5>PDF Export</h5>
                            <p class="text-muted">Download reports as professional PDF documents</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <i class="fas fa-chart-line fa-3x text-success mb-2"></i>
                            <h5>Real-Time Data</h5>
                            <p class="text-muted">Always up-to-date with current database information</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <i class="fas fa-print fa-3x text-warning mb-2"></i>
                            <h5>Print Ready</h5>
                            <p class="text-muted">Formatted for professional printing</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
