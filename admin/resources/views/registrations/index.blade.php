@extends('layouts.app')

@section('title', 'Registrations')
@section('page-title', 'Registrations')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold">All Registrations</h3>
            
            <div class="flex space-x-4">
                <form method="GET" class="flex space-x-2">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search by name or email..." 
                        value="{{ request('search') }}"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    <select 
                        name="status" 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Paid</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="p-6">
        <!-- Showing records info -->
        <div class="mb-4 mt-2">
            <p class="text-sm text-gray-700 leading-5">
                Showing
                @if ($registrations->firstItem())
                    <span class="font-medium">{{ $registrations->firstItem() }}</span>
                    to
                    <span class="font-medium">{{ $registrations->lastItem() }}</span>
                @else
                    {{ $registrations->count() }}
                @endif
                of
                <span class="font-medium">{{ $registrations->total() }}</span>
                registrations
            </p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Package</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            @if(!in_array(auth('admin')->user()->role, ['executive']))
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marked By</th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($registrations as $registration)
                        @php
                            $isDelegate = $registration->package_id == config('app.delegate_package_id');
                            $canReceiveInvitation = $registration->isPaid() || ($isDelegate && $registration->status === 'approved');
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $registration->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $registration->user->full_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $registration->user->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $registration->package->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ number_format($registration->total_amount, 2) }}
                            </td>
                            @if(!in_array(auth('admin')->user()->role, ['executive']))
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($isDelegate)
                                    {{-- For delegates, show delegate status --}}
                                    @if($registration->status === 'approved')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-user-check mr-1"></i>Approved Delegate
                                        </span>
                                    @elseif($registration->status === 'rejected')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-user-times mr-1"></i>Rejected Delegate
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-hourglass-half mr-1"></i>Delegate Pending
                                        </span>
                                    @endif
                                @else
                                    {{-- For non-delegates, show payment status --}}
                                    @if($registration->isPaid())
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Paid
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>Pending Payment
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($registration->payment && $registration->payment->completed_by)
                                    <div class="flex items-center" title="Manually marked as paid by {{ $registration->payment->completedBy->full_name ?? $registration->payment->completedBy->username }}">
                                        <i class="fas fa-user-check text-green-600 mr-1"></i>
                                        <span>{{ $registration->payment->completedBy->username ?? 'Admin' }}</span>
                                    </div>
                                    @if($registration->payment->manual_payment_remarks)
                                        <div class="text-xs text-gray-400 mt-1" title="{{ $registration->payment->manual_payment_remarks }}">
                                            <i class="fas fa-comment-dots"></i> 
                                            {{ Str::limit($registration->payment->manual_payment_remarks, 30) }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('registrations.show', $registration) }}" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                
                                @can('markAsPaid', App\Models\Registration::class)
                                @if(!$registration->isPaid() && !$isDelegate)
                                <button type="button" 
                                        onclick="openMarkPaidModal({{ $registration->id }}, '{{ addslashes($registration->user->full_name) }}', '{{ $registration->total_amount }}')" 
                                        class="ml-3 text-orange-600 hover:text-orange-900"
                                        title="Mark as Paid">
                                    <i class="fas fa-money-bill-wave"></i> Mark Paid
                                </button>
                                @endif
                                @endcan
                                
                                @can('viewInvitation', App\Models\Registration::class)
                                @if($canReceiveInvitation)
                                <button type="button" 
                                        onclick="openPdfModal({{ $registration->id }})" 
                                        class="ml-3 text-purple-600 hover:text-purple-900"
                                        title="Preview Invitation">
                                    <i class="fas fa-file-pdf"></i> Preview
                                </button>
                                <a href="{{ route('invitations.download', $registration) }}" class="ml-3 text-green-600 hover:text-green-900" title="Download Invitation Letter">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                @endif
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ auth('admin')->user()->role === 'executive' ? '6' : '8' }}" class="px-6 py-4 text-center text-gray-500">No registrations found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $registrations->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Include PDF Preview Modal -->
@include('components.invitation-preview-modal')

<!-- Include Mark Paid Modal -->
@include('components.mark-paid-modal')

@endsection
