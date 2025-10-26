<?php $__env->startSection('title', 'Manage Delegates'); ?>
<?php $__env->startSection('page-title', 'Manage Delegates'); ?>

<?php $__env->startSection('content'); ?>
<!-- Status Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 font-medium">Pending Review</p>
                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo e($statusCounts['pending'] ?? 0); ?></p>
            </div>
            <div class="bg-yellow-100 rounded-full p-3">
                <i class="fas fa-clock text-2xl text-yellow-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 font-medium">Approved</p>
                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo e($statusCounts['approved'] ?? 0); ?></p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-check-circle text-2xl text-green-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 font-medium">Rejected</p>
                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo e($statusCounts['rejected'] ?? 0); ?></p>
            </div>
            <div class="bg-red-100 rounded-full p-3">
                <i class="fas fa-times-circle text-2xl text-red-600"></i>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <h3 class="text-lg font-semibold mb-4">Delegate Registrations</h3>

        <!-- Responsive Filter Form -->
        <form method="GET" class="bg-gray-50 p-4 rounded-lg">
            <!-- Mobile: Stack vertically, Desktop: Grid layout -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
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
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                    >
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-flag mr-1"></i>Status
                    </label>
                    <select 
                        name="status" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                    >
                        <option value="">All Status</option>
                        <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="approved" <?php echo e(request('status') === 'approved' ? 'selected' : ''); ?>>Approved</option>
                        <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                    </select>
                </div>

                <!-- Delegate Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-users mr-1"></i>Category
                    </label>
                    <select 
                        name="delegate_category" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                    >
                        <option value="">All Categories</option>
                        <?php $__currentLoopData = $delegateCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category); ?>" <?php echo e(request('delegate_category') === $category ? 'selected' : ''); ?>>
                                <?php echo e($category); ?>

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
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
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
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-2 sm:justify-start">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-search mr-2"></i>Apply Filters
                </button>
                <a href="<?php echo e(route('delegates.index')); ?>" class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition-colors duration-200 text-sm font-medium text-center">
                    <i class="fas fa-times mr-2"></i>Clear Filters
                </a>
                <a href="<?php echo e(route('delegates.export', request()->query())); ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm font-medium text-center">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </a>
            </div>
        </form>

        <!-- Filter Summary -->
        <?php if(request()->hasAny(['search', 'status', 'delegate_category', 'country'])): ?>
        <div class="mt-4 flex flex-wrap gap-2">
            <span class="text-sm text-gray-600">Active filters:</span>
            <?php if(request('search')): ?>
                <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    Search: "<?php echo e(request('search')); ?>"
                </span>
            <?php endif; ?>
            <?php if(request('status')): ?>
                <span class="inline-flex items-center px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">
                    Status: <?php echo e(ucfirst(request('status'))); ?>

                </span>
            <?php endif; ?>
            <?php if(request('delegate_category')): ?>
                <span class="inline-flex items-center px-3 py-1 bg-orange-100 text-orange-800 text-sm rounded-full">
                    Category: <?php echo e(request('delegate_category')); ?>

                </span>
            <?php endif; ?>
            <?php if(request('country')): ?>
                <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                    Country: <?php echo e(request('country')); ?>

                </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="p-6 pt-8">
    <!-- Showing records info and per-page selector -->
    <div class="mb-4 mt-2 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <p class="text-sm text-gray-700 leading-5">
                Showing
                <?php if($delegates->firstItem()): ?>
                    <span class="font-medium"><?php echo e($delegates->firstItem()); ?></span>
                    to
                    <span class="font-medium"><?php echo e($delegates->lastItem()); ?></span>
                <?php else: ?>
                    <?php echo e($delegates->count()); ?>

                <?php endif; ?>
                of
                <span class="font-medium"><?php echo e($delegates->total()); ?></span>
                delegates
            </p>
        
        <!-- Per-page selector -->
        <?php if (isset($component)) { $__componentOriginal720c5d99204acad589a79c73de989541 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal720c5d99204acad589a79c73de989541 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.per-page-selector','data' => ['paginator' => $delegates,'currentPerPage' => request('per_page', 50)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('per-page-selector'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($delegates),'current-per-page' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('per_page', 50))]); ?>
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
                            <th class="w-12 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="w-20 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">REG ID</th>
                            <th class="w-1/5 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Name</span>
                                    <?php if(request('sort') == 'name'): ?>
                                        <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort text-xs opacity-50"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="w-1/4 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'email', 'direction' => request('sort') == 'email' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Email</span>
                                    <?php if(request('sort') == 'email'): ?>
                                        <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort text-xs opacity-50"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="w-1/6 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'delegate_category', 'direction' => request('sort') == 'delegate_category' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Category</span>
                                    <?php if(request('sort') == 'delegate_category'): ?>
                                        <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort text-xs opacity-50"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="w-24 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Status</span>
                                    <?php if(request('sort') == 'status'): ?>
                                        <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort text-xs opacity-50"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="w-28 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Date</span>
                                    <?php if(request('sort') == 'created_at'): ?>
                                        <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort text-xs opacity-50"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="w-20 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $delegates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $delegate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo e($delegates->firstItem() + $index); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e($delegate->id); ?>

                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="text-sm font-medium text-gray-900 break-words"><?php echo e($delegate->user->full_name); ?></div>
                                <div class="text-sm text-gray-500 break-words"><?php echo e($delegate->user->organization ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 break-words">
                                <?php echo e($delegate->user->email); ?>

                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 break-words">
                                <?php echo e($delegate->user->delegate_category ?? 'N/A'); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($delegate->status === 'pending'): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i>Pending
                                    </span>
                                <?php elseif($delegate->status === 'approved'): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Approved
                                    </span>
                                <?php elseif($delegate->status === 'rejected'): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-times mr-1"></i>Rejected
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        <?php echo e(ucfirst($delegate->status)); ?>

                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo e($delegate->created_at ? $delegate->created_at->format('M d, Y') : 'N/A'); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="<?php echo e(route('delegates.show', $delegate)); ?>" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manageDelegates', App\Models\Registration::class)): ?>
                                    <?php if($delegate->status === 'pending'): ?>
                                        <button type="button" 
                                                onclick="quickApprove(<?php echo e($delegate->id); ?>)" 
                                                class="ml-3 text-green-600 hover:text-green-900"
                                                title="Quick Approve">
                                            <i class="fas fa-check-circle"></i> Approve
                                        </button>
                                        <button type="button" 
                                                onclick="openRejectModal(<?php echo e($delegate->id); ?>, '<?php echo e($delegate->user->full_name); ?>')" 
                                                class="ml-3 text-red-600 hover:text-red-900"
                                                title="Reject">
                                            <i class="fas fa-times-circle"></i> Reject
                                        </button>
                                    <?php elseif(auth('admin')->user()->role === 'admin'): ?>
                                        <?php if($delegate->status === 'approved'): ?>
                                            <button type="button" 
                                                    onclick="resetToPending(<?php echo e($delegate->id); ?>, 'Cancel Approval', 'Are you sure you want to cancel the approval for <?php echo e(addslashes($delegate->user->full_name)); ?>? This will move them back to pending status.')" 
                                                    class="ml-3 text-orange-600 hover:text-orange-900"
                                                    title="Cancel Approval">
                                                <i class="fas fa-undo"></i> Cancel Approval
                                            </button>
                                        <?php elseif($delegate->status === 'rejected'): ?>
                                            <button type="button" 
                                                    onclick="resetToPending(<?php echo e($delegate->id); ?>, 'Recall Rejection', 'Are you sure you want to recall the rejection for <?php echo e(addslashes($delegate->user->full_name)); ?>? This will move them back to pending status.')" 
                                                    class="ml-3 text-blue-600 hover:text-blue-900"
                                                    title="Recall Rejection">
                                                <i class="fas fa-undo"></i> Recall Rejection
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if($delegate->status === 'approved'): ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewInvitation', App\Models\Registration::class)): ?>
                                        <button type="button" 
                                                onclick="openPdfModal(<?php echo e($delegate->id); ?>)" 
                                                class="ml-3 text-purple-600 hover:text-purple-900"
                                                title="Preview Invitation">
                                            <i class="fas fa-file-pdf"></i> Preview
                                        </button>
                                        <a href="<?php echo e(route('invitations.download', $delegate)); ?>" class="ml-3 text-green-600 hover:text-green-900" title="Download Invitation">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No delegate registrations found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>

            <div class="mt-6">
                <?php echo e($delegates->appends(request()->query())->links()); ?>

            </div>
        </div>
    </div>
</div>

<!-- Include PDF Preview Modal -->
<?php echo $__env->make('components.invitation-preview-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- Include Reject Delegate Modal -->
<?php echo $__env->make('components.reject-delegate-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Quick approve function
function quickApprove(delegateId) {
    if (!confirm('Are you sure you want to approve this delegate?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `<?php echo e(url('delegates')); ?>/${delegateId}/approve`;
    
    const token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = '<?php echo e(csrf_token()); ?>';
    form.appendChild(token);
    
    document.body.appendChild(form);
    form.submit();
}

// Reset to pending function (cancel approval or recall rejection)
function resetToPending(delegateId, actionTitle, confirmMessage) {
    if (!confirm(confirmMessage)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `<?php echo e(url('delegates')); ?>/${delegateId}/reset-to-pending`;
    
    const token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = '<?php echo e(csrf_token()); ?>';
    form.appendChild(token);
    
    document.body.appendChild(form);
    form.submit();
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/delegates/index.blade.php ENDPATH**/ ?>