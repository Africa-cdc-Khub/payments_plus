@extends('layouts.app')

@section('title', 'Edit Admin')
@section('page-title', 'Edit Admin: ' . $admin->username)

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <form method="POST" action="{{ route('admins.update', $admin) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username <span class="text-red-500">*</span></label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    value="{{ old('username', $admin->username) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('username') border-red-500 @enderror"
                    required
                >
                @error('username')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email', $admin->email) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                    required
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                <input 
                    type="text" 
                    id="full_name" 
                    name="full_name" 
                    value="{{ old('full_name', $admin->full_name) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('full_name') border-red-500 @enderror"
                    required
                >
                @error('full_name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role <span class="text-red-500">*</span></label>
                <select 
                    id="role" 
                    name="role" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('role') border-red-500 @enderror"
                    required
                >
                    <option value="admin" {{ old('role', $admin->role) === 'admin' ? 'selected' : '' }}>Admin (Full Access)</option>
                    <option value="secretariat" {{ old('role', $admin->role) === 'secretariat' ? 'selected' : '' }}>Secretariat (Delegates & Invitations)</option>
                    <option value="finance" {{ old('role', $admin->role) === 'finance' ? 'selected' : '' }}>Finance (Payments Only)</option>
                    <option value="executive" {{ old('role', $admin->role) === 'executive' ? 'selected' : '' }}>Executive (View Only)</option>
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">
                    <strong>Admin:</strong> Full system access | 
                    <strong>Secretariat:</strong> Manage delegates, send invitations | 
                    <strong>Finance:</strong> View all payments | 
                    <strong>Executive:</strong> View approved delegates & completed payments only
                </p>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password <small>(leave blank to keep current)</small></label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror"
                >
                @error('password')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>
        </div>

        <div class="mt-6">
            <label class="flex items-center">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $admin->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">Active</span>
            </label>
        </div>

        <div class="mt-8 flex justify-end space-x-4">
            <a href="{{ route('admins.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Update Admin
            </button>
        </div>
    </form>
</div>
@endsection

