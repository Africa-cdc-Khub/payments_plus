@extends('layouts.app')

@section('title', 'Admins')
@section('page-title', 'Admin Management')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b flex justify-between items-center">
        <h3 class="text-lg font-semibold">All Admins</h3>
        <a href="{{ route('admins.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus"></i> Add New Admin
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Full Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Login</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($admins as $admin)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $admin->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $admin->username }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $admin->full_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $admin->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($admin->role === 'admin')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                <i class="fas fa-crown mr-1"></i>Admin
                            </span>
                        @elseif($admin->role === 'secretariat')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                <i class="fas fa-users mr-1"></i>Secretariat
                            </span>
                        @elseif($admin->role === 'finance')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-dollar-sign mr-1"></i>Finance
                            </span>
                        @elseif($admin->role === 'executive')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-eye mr-1"></i>Executive
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($admin->role) }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($admin->is_active)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $admin->last_login?->format('M d, Y H:i') ?? 'Never' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('admins.edit', $admin) }}" class="text-blue-600 hover:text-blue-900" title="Edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            @if($admin->id !== auth('admin')->id())
                            <form method="POST" action="{{ route('admins.reset-password', $admin) }}" class="inline" onsubmit="return confirm('Reset password for {{ $admin->full_name }}? A new password will be sent to their email.');">
                                @csrf
                                <button type="submit" class="text-orange-600 hover:text-orange-900" title="Reset Password">
                                    <i class="fas fa-key"></i> Reset
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admins.destroy', $admin) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this admin?')" title="Delete">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">No admins found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-6">
        {{ $admins->links() }}
    </div>
</div>
@endsection

