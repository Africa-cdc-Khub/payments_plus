<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Portal') - CPHIA 2025</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @stack('styles')
    
    <style>
        /* Mobile sidebar overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 40;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
        
        /* Sidebar responsive behavior */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -256px;
                height: 100vh;
                z-index: 50;
                transition: left 0.3s ease;
            }
            
            .sidebar.open {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
        
        @media (min-width: 769px) {
            .sidebar {
                position: relative;
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Mobile sidebar overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    
    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside class="sidebar w-64 bg-gray-800 text-white flex-shrink-0" id="sidebar">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">CPHIA 2025</h1>
                        <p class="text-gray-400 text-sm">Admin Portal</p>
                    </div>
                    <!-- Mobile close button -->
                    <button class="md:hidden text-gray-400 hover:text-white" onclick="toggleSidebar()">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <nav class="mt-6">
                @php
                    $admin = auth('admin')->user();
                @endphp
                
                @if($admin && in_array($admin->role, ['admin', 'secretariat', 'finance']))
                <a href="{{ route('dashboard') }}" class="block px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('dashboard') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-home mr-2"></i> Dashboard
                </a>
                @endif
                
                @if($admin && in_array($admin->role, ['admin', 'secretariat', 'finance']))
                <a href="{{ route('registrations.index') }}" class="block px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('registrations.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-users mr-2"></i> Registrations
                </a>
                @endif
                
                @if($admin && in_array($admin->role, ['admin', 'secretariat']))
                <a href="{{ route('delegates.index') }}" class="block px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('delegates.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-user-check mr-2"></i> Manage Delegates
                </a>
                @endif
                
                @if($admin && in_array($admin->role, ['admin', 'travels','secretariat']))
                <a href="{{ route('approved-delegates.index') }}" class="block px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('approved-delegates.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-check-circle mr-2"></i> Approved Delegates
                </a>
                @endif
                
                @if($admin && in_array($admin->role, ['admin', 'secretariat', 'finance']))
                <a href="{{ route('payments.index') }}" class="block px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('payments.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-credit-card mr-2"></i> Payments
                </a>
                @endif
                
                @if($admin && $admin->role === 'admin')
                <a href="{{ route('invoices.index') }}" class="block px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('invoices.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-file-invoice mr-2"></i> Invoices
                </a>
                @endif
                
                @if($admin && in_array($admin->role, ['admin', 'executive','secretariat','finance','hosts']))
                <a href="{{ route('participants.index') }}" class="block px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('participants.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-users mr-2"></i> Participants
                </a>
                @endif
                
                @if($admin && $admin->role === 'admin')
                <a href="{{ route('packages.index') }}" class="block px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('packages.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-box mr-2"></i> Packages
                </a>
                @endif
                
                @if($admin && $admin->role === 'admin')
                <a href="{{ route('admins.index') }}" class="block px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('admins.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-user-shield mr-2"></i> Admins
                </a>
                @endif
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center mr-2">
                        <!-- Mobile menu button -->
                        <button class="md:hidden mr-3 text-gray-600 hover:text-gray-800" onclick="toggleSidebar()">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h2 class="text-xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
                    </div>
                    
                    <div class="flex items-center space-x-2 md:space-x-4">
                        <span class="text-gray-700 text-sm md:text-base hidden sm:block">{{ auth('admin')->user()->full_name }}</span>
                        <a href="{{ route('change-password') }}" class="text-blue-600 hover:text-blue-800 text-sm md:text-base" title="Change Password">
                            <i class="fas fa-key mr-1"></i> <span class="hidden sm:inline">Change Password</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm md:text-base" title="Logout">
                                <i class="fas fa-sign-out-alt mr-1"></i> <span class="hidden sm:inline">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-3 md:p-6">
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
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }
        
        // Close sidebar when clicking on navigation links on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('aside nav a');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    // Only close on mobile devices
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('open');
                        overlay.classList.remove('active');
                    }
                });
            });
            
            // Close sidebar when window is resized to desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('active');
                }
            });
        });
    </script>
    
</body>
</html>


