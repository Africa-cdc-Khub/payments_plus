@extends('layouts.admin')

@section('title', 'Summary Report')
@section('page-title', 'Summary Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Summary</li>
@endsection

@section('content')
    <!-- Filters -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title"><i class="fas fa-filter"></i> Date Range</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.reports.summary') }}" method="GET" class="form-inline">
                        <div class="form-group mr-2">
                            <label for="date_from" class="mr-2">From:</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $date_from }}">
                        </div>
                        <div class="form-group mr-2">
                            <label for="date_to" class="mr-2">To:</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $date_to }}">
                        </div>
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Generate
                        </button>
                        <a href="{{ route('admin.reports.summary', array_merge(request()->all(), ['format' => 'pdf'])) }}" class="btn btn-danger mr-2" target="_blank">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </a>
                        <button type="button" onclick="window.print()" class="btn btn-secondary">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $registrations['total'] }}</h3>
                    <p>Total Registrations</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($payments['completed'], 2) }}</h3>
                    <p>Revenue (Completed)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $participants['total'] }}</h3>
                    <p>Total Participants</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $participants['requires_visa'] }}</h3>
                    <p>Require Visa</p>
                </div>
                <div class="icon">
                    <i class="fas fa-passport"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics -->
    <div class="row">
        <!-- Registrations -->
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt"></i> Registrations</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Total:</th>
                            <td><strong>{{ $registrations['total'] }}</strong></td>
                        </tr>
                        <tr>
                            <th>Individual:</th>
                            <td>{{ $registrations['individual'] }}</td>
                        </tr>
                        <tr>
                            <th>Group:</th>
                            <td>{{ $registrations['group'] }}</td>
                        </tr>
                        <tr>
                            <th>Completed:</th>
                            <td><span class="badge badge-success">{{ $registrations['completed'] }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payments -->
        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-dollar-sign"></i> Payments</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Total Amount:</th>
                            <td><strong>${{ number_format($payments['total'], 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Completed:</th>
                            <td><span class="text-success">${{ number_format($payments['completed'], 2) }}</span></td>
                        </tr>
                        <tr>
                            <th>Pending:</th>
                            <td><span class="text-warning">${{ number_format($payments['pending'], 2) }}</span></td>
                        </tr>
                        <tr>
                            <th>Transactions:</th>
                            <td>{{ $payments['count'] }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Participants -->
        <div class="col-md-4">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users"></i> Participants</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Total:</th>
                            <td><strong>{{ $participants['total'] }}</strong></td>
                        </tr>
                        <tr>
                            <th>Require Visa:</th>
                            <td>{{ $participants['requires_visa'] }}</td>
                        </tr>
                        <tr>
                            <th>Present:</th>
                            <td><span class="badge badge-success">{{ $participants['present'] }}</span></td>
                        </tr>
                        <tr>
                            <th>Absent:</th>
                            <td><span class="badge badge-danger">{{ $participants['absent'] }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Info -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-0">
                        <i class="fas fa-calendar"></i> Report Period: {{ \Carbon\Carbon::parse($date_from)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($date_to)->format('M d, Y') }}
                        <br>
                        <i class="fas fa-clock"></i> Generated: {{ $generated_at }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row no-print">
        <div class="col-12">
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>
@endsection

@push('styles')
<style>
    @media print {
        .main-sidebar, .main-header, .content-header, .btn, .breadcrumb, .no-print, .card-header.bg-primary {
            display: none !important;
        }
        .content-wrapper {
            margin: 0 !important;
        }
    }
</style>
@endpush

