<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CPHIA 2025</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #1e40af 0%, #0f766e 50%, #059669 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .floating-animation {
            animation: float 8s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(5deg); }
        }
        .pulse-animation {
            animation: pulse 3s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.9; transform: scale(1.02); }
        }
        .africa-cdc-blue {
            color: #1e40af;
        }
        .cphia-green {
            color: #059669;
        }
        .accent-blue {
            background-color: #1e40af;
        }
        .accent-green {
            background-color: #059669;
        }
        /* Debug styles for button visibility */
        button[type="submit"] {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            z-index: 10 !important;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <!-- Background decorative elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-100 opacity-20 rounded-full floating-animation"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-emerald-100 opacity-20 rounded-full floating-animation" style="animation-delay: -4s;"></div>
        <div class="absolute top-1/2 left-1/4 w-32 h-32 bg-teal-100 opacity-15 rounded-full floating-animation" style="animation-delay: -2s;"></div>
        <div class="absolute top-1/4 right-1/3 w-24 h-24 bg-blue-200 opacity-10 rounded-full floating-animation" style="animation-delay: -6s;"></div>
    </div>

    <div class="relative z-10 w-full max-w-md">
        <!-- Main login card -->
        <div class="glass-effect rounded-2xl shadow-2xl p-8">
            <!-- Logo and header -->
            <div class="text-center mb-8">
                <div class="flex justify-center items-center space-x-8 mb-6">
                    <div class="text-center">
                        <img src="<?php echo e(asset('images/logo.png')); ?>" 
                             alt="Africa CDC" 
                             class="h-14 w-auto mx-auto opacity-90 hover:opacity-100 transition-all duration-300 hover:scale-105">
                       
                    </div>
                    <div class="w-px h-16 bg-gradient-to-b from-blue-300 to-emerald-300"></div>
                    <div class="text-center">
                        <img src="<?php echo e(asset('images/CPHIA-2025-logo_reverse_small.png')); ?>" 
                             alt="CPHIA 2025" 
                             class="h-16 w-auto mx-auto pulse-animation">
                    </div>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Admin Portal</h1>
                <p class="text-gray-600 text-lg">4th International Conference on Public Health in Africa</p>
                <div class="w-32 h-1 bg-gradient-to-r from-blue-600 via-teal-500 to-emerald-600 mx-auto mt-4 rounded-full"></div>
            </div>

            <!-- Error messages -->
            <?php if(session('error')): ?>
                <div class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <div><?php echo e(session('error')); ?></div>
                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-exclamation-circle mr-3"></i>
                        <span class="font-medium">Please correct the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside ml-6">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="text-sm"><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Login form -->
            <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-6">
                <?php echo csrf_field(); ?>

                <!-- Username field -->
                <div class="space-y-2">
                    <label for="username" class="block text-sm font-semibold text-gray-700">
                        <i class="fas fa-user mr-2 africa-cdc-blue"></i>Username
                    </label>
                    <div class="relative">
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="<?php echo e(old('username')); ?>"
                            class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="Enter your username"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- Password field -->
                <div class="space-y-2">
                    <label for="password" class="block text-sm font-semibold text-gray-700">
                        <i class="fas fa-lock mr-2 cphia-green"></i>Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600 transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="Enter your password"
                            required
                        >
                    </div>
                </div>

                <!-- Remember me checkbox -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-600 focus:ring-2">
                        <span class="ml-3 text-sm text-gray-700 font-medium">Remember me</span>
                    </label>
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200">
                        Forgot password?
                    </a>
                </div>

                <!-- Submit button -->
                <div class="w-full">
                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 text-white py-3 px-6 rounded-xl font-semibold hover:bg-blue-700 transition-all duration-200 flex items-center justify-center"
                        style="min-height: 48px; background: linear-gradient(135deg, #1e40af, #059669);"
                    >
                        <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                    </button>
                </div>
            </form>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-500">
                    © 2025 CPHIA Admin Portal. All rights reserved.
                </p>
                <div class="flex justify-center items-center mt-4 space-x-4">
                    <div class="flex items-center text-xs text-blue-600">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Secure Login
                    </div>
                    <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                    <div class="flex items-center text-xs text-emerald-600">
                        <i class="fas fa-lock mr-1"></i>
                        Encrypted
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading overlay (hidden by default) -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl p-8 flex items-center space-x-4 shadow-2xl">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="text-gray-700 font-medium">Signing in...</span>
        </div>
    </div>

    <script>
        // Debug: Check if button exists
        document.addEventListener('DOMContentLoaded', function() {
            const submitButton = document.querySelector('button[type="submit"]');
            console.log('Submit button found:', submitButton);
            if (submitButton) {
                console.log('Button styles:', window.getComputedStyle(submitButton));
                console.log('Button display:', submitButton.style.display);
                console.log('Button visibility:', submitButton.style.visibility);
            } else {
                console.error('Submit button not found!');
            }
        });

        // Add loading state to form submission
        document.querySelector('form').addEventListener('submit', function() {
            document.getElementById('loadingOverlay').classList.remove('hidden');
        });

        // Add some interactive effects
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                if (this.id === 'username') {
                    this.parentElement.classList.add('ring-2', 'ring-blue-600');
                } else if (this.id === 'password') {
                    this.parentElement.classList.add('ring-2', 'ring-emerald-600');
                }
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('ring-2', 'ring-blue-600', 'ring-emerald-600');
            });
        });
    </script>
</body>
</html>

<?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/auth/login.blade.php ENDPATH**/ ?>