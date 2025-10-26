<?php $__env->startSection('title', 'Payments'); ?>
<?php $__env->startSection('page-title', 'Payment Transactions'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">All Payments</h3>
            
            <form method="GET" action="<?php echo e(route('payments.export')); ?>">
                <?php if(request('search')): ?>
                    <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">
                <?php endif; ?>
                <?php if(request('package_id')): ?>
                    <input type="hidden" name="package_id" value="<?php echo e(request('package_id')); ?>">
                <?php endif; ?>
                <?php if(request('country')): ?>
                    <input type="hidden" name="country" value="<?php echo e(request('country')); ?>">
                <?php endif; ?>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-file-csv"></i> Export CSV
                </button>
            </form>
        </div>

        <!-- Filter Form - Always Visible -->
        <!-- Responsive Filter Form -->
        <form method="GET" class="bg-gray-50 p-4 rounded-lg">
            <!-- Mobile: Stack vertically, Desktop: Grid layout -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <!-- Search Field -->
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1"></i>Search
                    </label>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Name or email..." 
                        value="<?php echo e(request('search')); ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                </div>

                <!-- Package Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-box mr-1"></i>Package
                    </label>
                    <select 
                        name="package_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                        <option value="">All Packages</option>
                        <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($package->id); ?>" <?php echo e(request('package_id') == $package->id ? 'selected' : ''); ?>>
                                <?php echo e($package->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Country Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-globe mr-1"></i>Country
                    </label>
                    <select 
                        name="country" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                        <option value="">All Countries</option>
                        <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($country); ?>" <?php echo e(request('country') === $country ? 'selected' : ''); ?>>
                                <?php echo e($country); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <!-- Action Buttons - Responsive Layout -->
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-2">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-search mr-2"></i>Apply Filters
                </button>
                <a href="<?php echo e(route('payments.index')); ?>" class="flex-1 sm:flex-none px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition-colors duration-200 text-sm font-medium text-center">
                    <i class="fas fa-times mr-2"></i>Clear Filters
                </a>
                <a href="<?php echo e(route('payments.export', request()->query())); ?>" class="flex-1 sm:flex-none px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 text-sm font-medium text-center">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </a>
            </div>
        </form>

        <!-- Filter Summary -->
        <?php if(request()->hasAny(['search', 'package_id', 'country'])): ?>
        <div class="mt-4 flex flex-wrap gap-2 mt-2">
            <span class="text-sm text-gray-600">Active filters:</span>
            <?php if(request('search')): ?>
                <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    Search: "<?php echo e(request('search')); ?>"
                </span>
            <?php endif; ?>
            <?php if(request('package_id')): ?>
                <?php
                    $selectedPackage = $packages->find(request('package_id'));
                ?>
                <?php if($selectedPackage): ?>
                    <span class="inline-flex items-center px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">
                        Package: <?php echo e($selectedPackage->name); ?>

                    </span>
                <?php endif; ?>
            <?php endif; ?>
            <?php if(request('country')): ?>
                <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                    Country: <?php echo e(request('country')); ?>

                </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Statistics Summary -->
        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-2">
                <div class="flex items-center py-2">
                    <div class="bg-blue-100 rounded-full p-3 mr-3">
                        <i class="fas fa-dollar-sign text-xl text-blue-600"></i>
                    </div>
                    <div class="py-2">
                        <p class="text-sm text-gray-600">Total Payments</p>
                        <p class="text-2xl font-bold text-gray-900">$<?php echo e(number_format($totalPaymentAmount, 2)); ?></p>
                    </div>
                </div>
            </div>

            <?php if(request('package_id')): ?>
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="bg-purple-100 rounded-full p-3 mr-3">
                        <i class="fas fa-box text-xl text-purple-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Filtered Package</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e($packages->find(request('package_id'))->name ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if(request('country')): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-full p-3 mr-3">
                        <i class="fas fa-globe text-xl text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Filtered Country</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e(request('country')); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Showing records info and per-page selector -->
    <div class="mb-4 mt-2 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <p class="text-sm text-gray-700 leading-5">
            Showing
            <?php if($payments->firstItem()): ?>
                <span class="font-medium"><?php echo e($payments->firstItem()); ?></span>
                to
                <span class="font-medium"><?php echo e($payments->lastItem()); ?></span>
            <?php else: ?>
                <?php echo e($payments->count()); ?>

            <?php endif; ?>
            of
            <span class="font-medium"><?php echo e($payments->total()); ?></span>
            payments
        </p>
        
        <!-- Per-page selector -->
        <?php if (isset($component)) { $__componentOriginal720c5d99204acad589a79c73de989541 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal720c5d99204acad589a79c73de989541 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.per-page-selector','data' => ['paginator' => $payments,'currentPerPage' => request('per_page', 50)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('per-page-selector'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($payments),'current-per-page' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('per_page', 50))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal720c5d99204acad589a79c73de989541)): ?>
<?php $attributes = $__attributesOriginal720c5d99204acad589a79c73de989541; ?>
<?php unset($__attributesOriginal720c5d99204acad589a79c73de989541); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal720c5d99204acad589a79c73de989541)): ?>
<?php $component = $__componentOriginal720c5d99204acad589a79c73de989541; ?>
<?php unset($__componentOriginal720c5d99204acad589a79c73de989541); ?>
<?php endif; ?>
    </div>

    <div class="table-container">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full table-fixed">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Name</span>
                            <?php if(request('sort') == 'name'): ?>
                                <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                            <?php else: ?>
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'email', 'direction' => request('sort') == 'email' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Email</span>
                            <?php if(request('sort') == 'email'): ?>
                                <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                            <?php else: ?>
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'country', 'direction' => request('sort') == 'country' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Country</span>
                            <?php if(request('sort') == 'country'): ?>
                                <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                            <?php else: ?>
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'package', 'direction' => request('sort') == 'package' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Package</span>
                            <?php if(request('sort') == 'package'): ?>
                                <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                            <?php else: ?>
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Passport No.</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Airport of Origin</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'amount', 'direction' => request('sort') == 'amount' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Amount</span>
                            <?php if(request('sort') == 'amount'): ?>
                                <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                            <?php else: ?>
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'payment_completed_at', 'direction' => request('sort') == 'payment_completed_at' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Date</span>
                            <?php if(request('sort') == 'payment_completed_at'): ?>
                                <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                            <?php else: ?>
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo e($payments->firstItem() + $index); ?>

                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo e($payment->id); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                        <?php echo e($payment->user->full_name); ?>

                        
                        
                        <?php if(in_array(auth('admin')->user()->role, ['admin', 'secretariat']) && $payment->registration_type !== 'individual'): ?>
                        <small><a href="<?php echo e(route('registration-participants.index', $payment)); ?>" 
                           class="ml-3 text-red-600 hover:text-red-900"
                           title="View Participants">
                            <i class="fas fa-users"></i> Participants
                        </a></small>
                        <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo e($payment->user->email); ?>

                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo e($payment->user->country ?? 'N/A'); ?>

                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo e($payment->package->name); ?>

                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php if(in_array(auth('admin')->user()->role, ['admin', 'travels'])): ?>
                            <?php echo e($payment->user->passport_number ?? '-'); ?>

                        <?php else: ?>
                            <span class="text-gray-400">••••••••</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php if(in_array(auth('admin')->user()->role, ['admin', 'travels'])): ?>
                            <?php echo e($payment->user->airport_of_origin ?? '-'); ?>

                        <?php else: ?>
                            <span class="text-gray-400">••••••••</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                        $<?php echo e(number_format($payment->total_amount ?? $payment->payment->amount ?? 0, 2)); ?>

                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo e($payment->payment ? ucfirst(str_replace('_', ' ', $payment->payment->payment_method)) : 'N/A'); ?>

                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo e($payment->payment && $payment->payment->payment_date ? $payment->payment->payment_date->format('M d, Y H:i') : 'N/A'); ?>

                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="<?php echo e(route('payments.show', $payment)); ?>" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="11" class="px-6 py-4 text-center text-gray-500">No payments found</td>
                </tr>
                <?php endif; ?>
            </tbody>
            </table>
        </div>
    </div>

    <div class="p-6">
        <?php echo e($payments->appends(request()->query())->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/payments/index.blade.php ENDPATH**/ ?>