@extends('layouts.app')

@section('title', 'Delegate Details')
@section('page-title', 'Delegate Details')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('delegates.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i> Back to Delegates
        </a>
    </div>

    <!-- Status Header -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-bold text-gray-900">{{ $registration->user->full_name }}</h3>
                <p class="text-gray-600 mt-1">Registration ID: #{{ $registration->id }}</p>
            </div>
            <div class="text-right">
                @if($registration->status === 'pending')
                    <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        <i class="fas fa-clock mr-2"></i> Pending Review
                    </span>
                @elseif($registration->status === 'approved')
                    <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        <i class="fas fa-check mr-2"></i> Approved
                    </span>
                @elseif($registration->status === 'rejected')
                    <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                        <i class="fas fa-times mr-2"></i> Rejected
                    </span>
                @endif
            </div>
        </div>

        @can('manageDelegates', App\Models\Registration::class)
        @if($registration->status === 'pending')
        <div class="mt-6 flex gap-3">
            <form method="POST" action="{{ route('delegates.approve', $registration) }}" class="inline">
                @csrf
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                        onclick="return confirm('Are you sure you want to approve this delegate registration?')">
                    <i class="fas fa-check-circle mr-2"></i>Approve Registration
                </button>
            </form>
            
            <button type="button" 
                    onclick="openRejectModal({{ $registration->id }}, '{{ $registration->user->full_name }}')" 
                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class="fas fa-times-circle mr-2"></i>Reject Registration
            </button>
        </div>
        @endif
        @endcan
    </div>

    <!-- Delegate Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Personal Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-user mr-2 text-blue-600"></i> Personal Information
            </h4>
            
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $registration->user->full_name }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $registration->user->email }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $registration->user->phone ?? 'N/A' }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Country</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $registration->user->country ?? 'N/A' }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Organization</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $registration->user->organization ?? 'N/A' }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Job Title</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $registration->user->job_title ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>

        <!-- Delegate Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-id-badge mr-2 text-blue-600"></i> Delegate Information
            </h4>
            
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Delegate Category</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $registration->user->delegate_category ?? 'N/A' }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Package</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $registration->package->name ?? 'N/A' }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Registration Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($registration->registration_type ?? 'N/A') }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Registered On</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $registration->created_at ? $registration->created_at->format('F d, Y h:i A') : 'N/A' }}
                    </dd>
                </div>
                
                @if($registration->status === 'rejected' && $registration->rejection_reason)
                <div>
                    <dt class="text-sm font-medium text-red-600">Rejection Reason</dt>
                    <dd class="mt-1 text-sm text-gray-900 bg-red-50 p-3 rounded">
                        {{ $registration->rejection_reason }}
                    </dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    <!-- Invitation Actions (for Approved Delegates) -->
    @can('viewInvitation', App\Models\Registration::class)
    @if($registration->status === 'approved')
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-envelope mr-2 text-blue-600"></i> Invitation Letter
        </h4>
        <p class="text-sm text-gray-600 mb-4">Generate and send the official invitation letter for this approved delegate.</p>
        <div class="flex gap-3">
            <button type="button" 
                    onclick="openPdfModal({{ $registration->id }})" 
                    class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 inline-flex items-center">
                <i class="fas fa-file-pdf mr-2"></i> Preview Invitation Letter
            </button>
            <a href="{{ route('invitations.download', $registration) }}" 
               class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 inline-flex items-center">
                <i class="fas fa-download mr-2"></i> Download PDF
            </a>
            @can('sendInvitation', App\Models\Registration::class)
            <form method="POST" action="{{ route('invitations.send') }}" class="inline">
                @csrf
                <input type="hidden" name="registration_ids[]" value="{{ $registration->id }}">
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                        onclick="return confirm('Are you sure you want to send the invitation email to {{ $registration->user->email }}?')">
                    <i class="fas fa-paper-plane mr-2"></i> Send Email
                </button>
            </form>
            @endcan
        </div>
    </div>
    @endif
    @endcan

    <!-- Additional Participants -->
    @if($registration->participants->count() > 0)
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-users mr-2 text-blue-600"></i> Additional Participants
        </h4>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($registration->participants as $participant)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $participant->first_name }} {{ $participant->last_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $participant->email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $participant->phone ?? 'N/A' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<!-- Include PDF Preview Modal -->
@include('components.invitation-preview-modal')

<!-- Include Reject Delegate Modal -->
@include('components.reject-delegate-modal')

@endsection

