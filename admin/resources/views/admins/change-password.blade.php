@extends('layouts.app')

@section('title', 'Change Password')
@section('page-title', 'Change Password')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-8">
        <div class="mb-8">
            <div class="flex items-center justify-center mb-4">
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-key text-blue-600 text-2xl"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 text-center mb-2">
                Change Your Password
            </h3>
            <p class="text-gray-600 text-center">
                Update your password to keep your account secure
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            <!-- Left Column: Form -->
            <div>
                <form method="POST" action="{{ route('change-password.update') }}">
                    @csrf

                    <div class="space-y-6">
                        <!-- Current Password -->
                        <div>
                            <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-lock text-gray-500 mr-2"></i>
                                Current Password <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="current_password" 
                                name="current_password" 
                                required
                                placeholder="Enter your current password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('current_password') border-red-500 @enderror"
                            >
                            @error('current_password')
                                <p class="mt-2 text-sm text-red-500 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-key text-gray-500 mr-2"></i>
                                New Password <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                minlength="8"
                                placeholder="Enter your new password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('password') border-red-500 @enderror"
                            >
                            @error('password')
                                <p class="mt-2 text-sm text-red-500 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-check-circle text-gray-500 mr-2"></i>
                                Confirm New Password <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                required
                                minlength="8"
                                placeholder="Confirm your new password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            >
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('dashboard') }}" class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-center transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Cancel
                        </a>
                        <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Column: Info -->
            <div class="space-y-6">
                <!-- Password Requirements Info -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6">
                    <div class="flex items-start">
                        <div class="bg-blue-100 p-2 rounded-lg mr-4">
                            <i class="fas fa-info-circle text-blue-600 text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-blue-900 mb-3">Password Requirements</h4>
                            <ul class="text-sm text-blue-800 space-y-2">
                                <li class="flex items-center">
                                    <i class="fas fa-check text-blue-600 mr-2"></i>
                                    Minimum 8 characters
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-blue-600 mr-2"></i>
                                    Mix of letters, numbers, and symbols
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-blue-600 mr-2"></i>
                                    Avoid common words or personal info
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Security Tips -->
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6">
                    <div class="flex items-start">
                        <div class="bg-amber-100 p-2 rounded-lg mr-4">
                            <i class="fas fa-shield-alt text-amber-600 text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-amber-900 mb-3">Security Tips</h4>
                            <ul class="text-sm text-amber-800 space-y-2">
                                <li class="flex items-start">
                                    <i class="fas fa-lock text-amber-600 mr-2 mt-1"></i>
                                    <span>Never share your password with anyone</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-user-secret text-amber-600 mr-2 mt-1"></i>
                                    <span>Use a unique password for this account</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-sync text-amber-600 mr-2 mt-1"></i>
                                    <span>Change your password regularly</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-exclamation-triangle text-amber-600 mr-2 mt-1"></i>
                                    <span>Change immediately if you suspect unauthorized access</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

