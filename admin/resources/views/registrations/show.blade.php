@extends('layouts.app')

@section('title', 'Registration Details')
@section('page-title', 'Registration #' . $registration->id)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- User Information -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">User Information</h3>
        <dl class="space-y-3">
            <div>
                <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                <dd class="text-sm text-gray-900">{{ $registration->user->title }} {{ $registration->user->full_name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Email</dt>
                <dd class="text-sm text-gray-900">{{ $registration->user->email }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                <dd class="text-sm text-gray-900">{{ $registration->user->phone ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Nationality</dt>
                <dd class="text-sm text-gray-900">{{ $registration->user->nationality }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Organization</dt>
                <dd class="text-sm text-gray-900">{{ $registration->user->organization ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Position</dt>
                <dd class="text-sm text-gray-900">{{ $registration->user->position ?? 'N/A' }}</dd>
            </div>
        </dl>
    </div>

    <!-- Registration Details -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Registration Details</h3>
        <dl class="space-y-3">
            <div>
                <dt class="text-sm font-medium text-gray-500">Package</dt>
                <dd class="text-sm text-gray-900">{{ $registration->package->name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Registration Type</dt>
                <dd class="text-sm text-gray-900">{{ ucfirst($registration->registration_type) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                <dd class="text-sm text-gray-900">${{ number_format($registration->total_amount, 2) }} {{ $registration->currency }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Payment Status</dt>
                <dd class="text-sm">
                    @if($registration->isPaid())
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                    @endif
                </dd>
            </div>
            @if($registration->payment_completed_at)
            <div>
                <dt class="text-sm font-medium text-gray-500">Payment Date</dt>
                <dd class="text-sm text-gray-900">{{ $registration->payment_completed_at->format('M d, Y H:i') }}</dd>
            </div>
            @endif
            @if($registration->payment_transaction_id)
            <div>
                <dt class="text-sm font-medium text-gray-500">Transaction ID</dt>
                <dd class="text-sm text-gray-900">{{ $registration->payment_transaction_id }}</dd>
            </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                <dd class="text-sm text-gray-900">{{ $registration->created_at->format('M d, Y H:i') }}</dd>
            </div>
        </dl>
    </div>
</div>

@if($registration->isPaid())
<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Invitation Actions</h3>
    <div class="flex space-x-4">
        <a href="{{ route('invitations.download', $registration) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-download"></i> Download Invitation Letter
        </a>
        <form method="POST" action="{{ route('invitations.send') }}" class="inline">
            @csrf
            <input type="hidden" name="registration_ids[]" value="{{ $registration->id }}">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-envelope"></i> Send Invitation Email
            </button>
        </form>
    </div>
</div>
@endif

<div class="mt-6">
    <a href="{{ route('registrations.index') }}" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left"></i> Back to Registrations
    </a>
</div>
@endsection

