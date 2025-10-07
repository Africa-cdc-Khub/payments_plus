<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Portal') - CPHIA 2025</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 text-white flex-shrink-0">
            <div class="p-6">
                <h1 class="text-2xl font-bold">CPHIA 2025</h1>
                <p class="text-gray-400 text-sm">Admin Portal</p>
            </div>
            
            <nav class="mt-6">
                <a href="{{ route('dashboard') }}" class="block px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('dashboard') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-home mr-2"></i> Dashboard
                </a>
                <a href="{{ route('registrations.index') }}" class="block px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('registrations.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-users mr-2"></i> Registrations
                </a>
                <a href="{{ route('payments.index') }}" class="block px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('payments.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-credit-card mr-2"></i> Payments
                </a>
                <a href="{{ route('packages.index') }}" class="block px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('packages.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-box mr-2"></i> Packages
                </a>
                <a href="{{ route('admins.index') }}" class="block px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('admins.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-user-shield mr-2"></i> Admins
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
                    
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-700">{{ auth('admin')->user()->full_name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-sign-out-alt mr-1"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

