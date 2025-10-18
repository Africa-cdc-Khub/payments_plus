@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php
    $admin = auth('admin')->user();
@endphp

@if($admin && in_array($admin->role, ['admin', 'travels','secretariat','finance']))
<!-- Main Stats Cards Row -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Stats Cards -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-users text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Total Registrations</p>
                <p class="text-2xl font-bold">{{ number_format($stats['total_participants']) }}</p>
                <p class="text-xs text-gray-400 mt-1">Includes group members</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-check-circle text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Paid Participants</p>
                <p class="text-2xl font-bold">{{ number_format($stats['paid_participants']) }}</p>
                <p class="text-xs text-gray-400 mt-1">Confirmed attendees</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-clock text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Pending Payments</p>
                <p class="text-2xl font-bold">{{ number_format($stats['pending_payments']) }}</p>
                <p class="text-xs text-gray-400 mt-1">Excluding delegates</p>
                @if($stats['pending_invoices_revenue'] > 0)
                <small class="text-gray-500 image.png">Invoice value: ${{ number_format($stats['pending_invoices_revenue'], 2) }}</small>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <i class="fas fa-dollar-sign text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Total Revenue</p>
                <p class="text-2xl font-bold">${{ number_format($stats['total_revenue'], 2) }}</p>
                @if($stats['pending_invoices_revenue'] > 0)
                <small class="text-gray-500 image.png">Invoice value: ${{ number_format($stats['paid_invoices_revenue'], 2) }}</small>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delegates Stats Row -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Delegates -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                <i class="fas fa-user-tie text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Total Delegates</p>
                <p class="text-xl font-bold text-indigo-600">{{ number_format($stats['delegates']['total']) }}</p>
            </div>
        </div>
    </div>
    
    <!-- Approved -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Approved Delegates</p>
                <p class="text-xl font-bold text-green-600">{{ number_format($stats['delegates']['approved']) }}</p>
            </div>
        </div>
    </div>
    
    <!-- Pending -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-hourglass-half text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Pending Delegates</p>
                <p class="text-xl font-bold text-yellow-600">{{ number_format($stats['delegates']['pending']) }}</p>
            </div>
        </div>
    </div>
    
    <!-- Rejected -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100 text-red-600">
                <i class="fas fa-times-circle text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Rejected Delegates</p>
                <p class="text-xl font-bold text-red-600">{{ number_format($stats['delegates']['rejected']) }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Tables -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Registrations -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold">Recent Registrations</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Package</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recent_registrations as $registration)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $registration->user->full_name }}</div>
                            <div class="text-sm text-gray-500">{{ $registration->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $registration->package->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($registration->payment_status === 'completed')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($registration->total_amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No registrations found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold">Recent Payments</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recent_payments as $payment)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $payment->user->full_name }}</div>
                            <div class="text-sm text-gray-500">{{ $payment->package->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                            ${{ number_format($payment->payment_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $payment->payment_completed_at?->format('M d, Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No payments found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold">You are not authorized to access this page</h3>
    <p>Use Menu to navigate to the desired page</p>
</div>
@endif


@endsection

