@extends('layouts.app')

@section('title', 'Payment Details')
@section('page-title', 'Payment #' . $payment->id)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- User Information -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">User Information</h3>
        <dl class="space-y-3">
            <div>
                <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                <dd class="text-sm text-gray-900">{{ $payment->user->title }} {{ $payment->user->full_name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Email</dt>
                <dd class="text-sm text-gray-900">{{ $payment->user->email }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                <dd class="text-sm text-gray-900">{{ $payment->user->phone ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Nationality</dt>
                <dd class="text-sm text-gray-900">{{ $payment->user->nationality }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Organization</dt>
                <dd class="text-sm text-gray-900">{{ $payment->user->organization ?? 'N/A' }}</dd>
            </div>
        </dl>
    </div>

    <!-- Payment Details -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Payment Details</h3>
        <dl class="space-y-3">
            <div>
                <dt class="text-sm font-medium text-gray-500">Registration ID</dt>
                <dd class="text-sm text-gray-900">{{ $payment->id }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Package</dt>
                <dd class="text-sm text-gray-900">{{ $payment->package->name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Amount Paid</dt>
                <dd class="text-sm text-gray-900 font-semibold text-lg">${{ number_format($payment->payment_amount, 2) }} {{ $payment->payment_currency }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                <dd class="text-sm text-gray-900">{{ ucfirst($payment->payment_method ?? 'N/A') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Payment Status</dt>
                <dd class="text-sm">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Payment Date</dt>
                <dd class="text-sm text-gray-900">{{ $payment->payment_completed_at?->format('F d, Y H:i') }}</dd>
            </div>
            @if($payment->payment_transaction_id)
            <div>
                <dt class="text-sm font-medium text-gray-500">Transaction ID</dt>
                <dd class="text-sm text-gray-900 font-mono">{{ $payment->payment_transaction_id }}</dd>
            </div>
            @endif
            @if($payment->payment_reference)
            <div>
                <dt class="text-sm font-medium text-gray-500">Payment Reference</dt>
                <dd class="text-sm text-gray-900 font-mono">{{ $payment->payment_reference }}</dd>
            </div>
            @endif
        </dl>
    </div>
</div>

<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Registration Details</h3>
    <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <dt class="text-sm font-medium text-gray-500">Registration Type</dt>
            <dd class="text-sm text-gray-900">{{ ucfirst($payment->registration_type) }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
            <dd class="text-sm text-gray-900">${{ number_format($payment->total_amount, 2) }} {{ $payment->currency }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Registration Date</dt>
            <dd class="text-sm text-gray-900">{{ $payment->created_at->format('M d, Y H:i') }}</dd>
        </div>
    </dl>
</div>

<div class="mt-6 flex justify-between items-center">
    <a href="{{ route('payments.index') }}" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left"></i> Back to Payments
    </a>
    
    <div class="space-x-4">
        <a href="{{ route('registrations.show', $payment) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-eye"></i> View Full Registration
        </a>
        <a href="{{ route('invitations.download', $payment) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            <i class="fas fa-download"></i> Download Invitation
        </a>
    </div>
</div>
@endsection
