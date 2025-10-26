@extends('layouts.app')

@section('title', 'Receipt Details')
@section('page-title', 'Receipt #RCP-' . str_pad($receipt->id, 6, '0', STR_PAD_LEFT))

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Receipt Information -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Receipt Information</h3>
        <dl class="space-y-3">
            <div>
                <dt class="text-sm font-medium text-gray-500">Receipt Number</dt>
                <dd class="text-sm text-gray-900">RCP-{{ str_pad($receipt->id, 6, '0', STR_PAD_LEFT) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Registration Type</dt>
                <dd class="text-sm text-gray-900">{{ ucfirst($receipt->registration_type) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Payment Status</dt>
                <dd class="text-sm">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        <i class="fas fa-check mr-1"></i>Completed
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Amount</dt>
                <dd class="text-sm text-gray-900">${{ number_format($receipt->total_amount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Package</dt>
                <dd class="text-sm text-gray-900">{{ $receipt->package->name }}</dd>
            </div>
            @if($receipt->payment_method)
            <div>
                <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                <dd class="text-sm text-gray-900">{{ ucfirst($receipt->payment_method) }}</dd>
            </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500">Registration Date</dt>
                <dd class="text-sm text-gray-900">{{ $receipt->created_at->format('M d, Y H:i') }}</dd>
            </div>
            @if($receipt->payment_completed_at)
            <div>
                <dt class="text-sm font-medium text-gray-500">Payment Date</dt>
                <dd class="text-sm text-gray-900">{{ $receipt->payment_completed_at->format('M d, Y H:i') }}</dd>
            </div>
            @endif
        </dl>
    </div>

    <!-- Participant Information -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Participant Information</h3>
        <dl class="space-y-3">
            <div>
                <dt class="text-sm font-medium text-gray-500">Name</dt>
                <dd class="text-sm text-gray-900">{{ $receipt->user->first_name }} {{ $receipt->user->last_name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Email</dt>
                <dd class="text-sm text-gray-900">{{ $receipt->user->email }}</dd>
            </div>
            @if($receipt->user->phone)
            <div>
                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                <dd class="text-sm text-gray-900">{{ $receipt->user->phone }}</dd>
            </div>
            @endif
            @if($receipt->user->organization)
            <div>
                <dt class="text-sm font-medium text-gray-500">Organization</dt>
                <dd class="text-sm text-gray-900">{{ $receipt->user->organization }}</dd>
            </div>
            @endif
            @if($receipt->user->institution)
            <div>
                <dt class="text-sm font-medium text-gray-500">Institution</dt>
                <dd class="text-sm text-gray-900">{{ $receipt->user->institution }}</dd>
            </div>
            @endif
        </dl>
    </div>
</div>

@if($receipt->registration_type === 'group' && $receipt->participants && $receipt->participants->count() > 0)
<!-- Group Participants -->
<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Group Participants ({{ $receipt->participants->count() }} total)</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($receipt->participants as $participant)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $participant->first_name }} {{ $participant->last_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $participant->email }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $participant->organization ?? 'N/A' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Actions -->
<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Actions</h3>
    <div class="flex space-x-4">
        <button type="button" 
                onclick="openReceiptModal({{ $receipt->id }})" 
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
            <i class="fas fa-eye"></i> Preview Receipt PDF
        </button>
        
        <a href="{{ route('receipts.download', $receipt) }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            <i class="fas fa-download"></i> Download PDF
        </a>
        
        <button type="button" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 send-receipt-btn"
                data-receipt-id="{{ $receipt->id }}"
                data-email="{{ $receipt->user->email }}">
            <i class="fas fa-paper-plane"></i> Send Receipt
        </button>
        
        <a href="{{ route('receipts.index') }}" class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">
            <i class="fas fa-arrow-left"></i> Back to Receipts
        </a>
    </div>
</div>

<!-- Include Receipt Preview Modal -->
@include('components.receipt-preview-modal')

<!-- Include Send Receipt Modal -->
@include('components.send-receipt-modal')

@endsection
