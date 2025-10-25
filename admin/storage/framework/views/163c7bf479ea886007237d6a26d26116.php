<?php $__env->startSection('title', 'Approved Delegates'); ?>
<?php $__env->startSection('page-title', 'Approved Delegates'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Approved Delegates List</h3>
            
            <form method="GET" action="<?php echo e(route('approved-delegates.export')); ?>" class="inline">
                <?php if(request('delegate_category')): ?>
                    <input type="hidden" name="delegate_category" value="<?php echo e(request('delegate_category')); ?>">
                <?php endif; ?>
                <?php if(request('country')): ?>
                    <input type="hidden" name="country" value="<?php echo e(request('country')); ?>">
                <?php endif; ?>
                <?php if(request('search')): ?>
                    <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">
                <?php endif; ?>
                <?php if(request('travel_processed') !== null): ?>
                    <input type="hidden" name="travel_processed" value="<?php echo e(request('travel_processed')); ?>">
                <?php endif; ?>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-file-csv"></i> Export CSV
                </button>
            </form>
        </div>

        <!-- Responsive Filter Form -->
        <form method="GET" class="bg-gray-50 p-4 rounded-lg mb-4">
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
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                </div>

                <!-- Delegate Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-users mr-1"></i>Category
                    </label>
                    <select 
                        name="delegate_category" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
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

                <!-- Travel Status Filter (for travels role) -->
                <?php if(auth('admin')->user()->role === 'travels'): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-plane mr-1"></i>Travel Status
                    </label>
                    <select 
                        name="travel_processed" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                        <option value="">All Status</option>
                        <option value="0" <?php echo e(request('travel_processed') === '0' ? 'selected' : ''); ?>>Unprocessed</option>
                        <option value="1" <?php echo e(request('travel_processed') === '1' ? 'selected' : ''); ?>>Processed</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons - Responsive Layout -->
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-2">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-search mr-2"></i>Apply Filters
                </button>
                <a href="<?php echo e(route('approved-delegates.index')); ?>" class="flex-1 sm:flex-none px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition-colors duration-200 text-sm font-medium text-center">
                    <i class="fas fa-times mr-2"></i>Clear Filters
                </a>
                <a href="<?php echo e(route('approved-delegates.export', request()->query())); ?>" class="flex-1 sm:flex-none px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 text-sm font-medium text-center">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </a>
            </div>
        </form>

        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-green-900">Total Approved</h4>
                        <p class="text-2xl font-bold text-green-700"><?php echo e($delegates->total()); ?></p>
                    </div>
                </div>
            </div>

            <?php if(request('delegate_category')): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-filter text-blue-600 text-2xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-900">Filtered Category</h4>
                        <p class="text-lg font-bold text-blue-700"><?php echo e(request('delegate_category')); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if(request('country')): ?>
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-globe text-purple-600 text-2xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-purple-900">Filtered Country</h4>
                        <p class="text-lg font-bold text-purple-700"><?php echo e(request('country')); ?></p>
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
            <?php if($delegates->firstItem()): ?>
                <span class="font-medium"><?php echo e($delegates->firstItem()); ?></span>
                to
                <span class="font-medium"><?php echo e($delegates->lastItem()); ?></span>
            <?php else: ?>
                <?php echo e($delegates->count()); ?>

            <?php endif; ?>
            of
            <span class="font-medium"><?php echo e($delegates->total()); ?></span>
            approved delegates
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
                    <th class="w-16 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="w-1/6 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Name</span>
                            <?php if(request('sort') == 'name'): ?>
                                <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                            <?php else: ?>
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="w-1/5 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'email', 'direction' => request('sort') == 'email' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Email</span>
                            <?php if(request('sort') == 'email'): ?>
                                <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                            <?php else: ?>
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="w-1/6 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                    <th class="w-20 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'country', 'direction' => request('sort') == 'country' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Country</span>
                            <?php if(request('sort') == 'country'): ?>
                                <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                            <?php else: ?>
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="w-20 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'delegate_category', 'direction' => request('sort') == 'delegate_category' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Category</span>
                            <?php if(request('sort') == 'delegate_category'): ?>
                                <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                            <?php else: ?>
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="w-24 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Passport No.</th>
                    <th class="w-28 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Airport of Origin</th>
                    <?php if(auth('admin')->user()->role === 'travels'): ?>
                    <th class="w-24 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'travel_status', 'direction' => request('sort') == 'travel_status' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Travel Status</span>
                            <?php if(request('sort') == 'travel_status'): ?>
                                <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                            <?php else: ?>
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <?php endif; ?>
                    <th class="w-28 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Approved Date</span>
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
                        <?php if($delegate->user->title): ?>
                            <div class="text-xs text-gray-500 break-words"><?php echo e($delegate->user->title); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 break-words">
                        <?php echo e($delegate->user->email); ?>

                    </td>
                    <td class="px-6 py-4 text-sm">
                        <div class="text-sm text-gray-900 break-words"><?php echo e($delegate->user->organization ?? '-'); ?></div>
                        <?php if($delegate->user->position): ?>
                            <div class="text-xs text-gray-500 break-words"><?php echo e($delegate->user->position); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 break-words">
                        <?php echo e($delegate->user->country ?? '-'); ?>

                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if($delegate->user->delegate_category): ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                <?php echo e($delegate->user->delegate_category); ?>

                            </span>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <?php if(in_array(auth('admin')->user()->role, ['admin', 'travels'])): ?>
                            <div class="flex flex-col space-y-1">
                                <span class="text-gray-600 break-words">
                                    <?php echo e($delegate->user->passport_number ?? '-'); ?>

                                </span>
                                
                                <?php if($delegate->user->passport_file): ?>
                                    <button 
                                        onclick="openPassportPreview('<?php echo e(env('PARENT_APP_URL')); ?>/uploads/passports/<?php echo e($delegate->user->passport_file); ?>')" 
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        <small class="text-xs text-gray-500">View</small>
                                    </button>
                                <?php endif; ?>
                                
                            </div>
                        <?php else: ?>
                            <span class="text-gray-400">••••••••</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 break-words">
                        <?php if(in_array(auth('admin')->user()->role, ['admin', 'travels'])): ?>
                            <?php echo e($delegate->user->airport_of_origin ?? '-'); ?>

                        <?php else: ?>
                            <span class="text-gray-400">••••••••</span>
                        <?php endif; ?>
                    </td>
                    <?php if(auth('admin')->user()->role === 'travels'): ?>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if($delegate->travel_processed): ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check-circle"></i> Processed
                            </span>
                        <?php else: ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock"></i> Pending
                            </span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo e($delegate->updated_at ? $delegate->updated_at->format('M d, Y') : '-'); ?>

                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="<?php echo e(route('delegates.show', $delegate)); ?>" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye"></i> View
                        </a>
                        
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewInvitation', App\Models\Registration::class)): ?>
                        <button type="button" 
                                onclick="openPdfModal(<?php echo e($delegate->id); ?>)" 
                                class="ml-3 text-purple-600 hover:text-purple-900"
                                title="Preview Invitation">
                            <i class="fas fa-file-pdf"></i> Invitation
                        </button>
                        <?php endif; ?>

                        <?php if(auth('admin')->user()->role === 'travels'): ?>
                        <button type="button"
                                onclick="openTravelProcessedModal(<?php echo e($delegate->id); ?>, '<?php echo e(addslashes($delegate->user->full_name)); ?>', <?php echo e($delegate->travel_processed ? 'true' : 'false'); ?>)"
                                class="ml-3 text-<?php echo e($delegate->travel_processed ? 'orange' : 'green'); ?>-600 hover:text-<?php echo e($delegate->travel_processed ? 'orange' : 'green'); ?>-900"
                                title="<?php echo e($delegate->travel_processed ? 'Mark as Unprocessed' : 'Mark as Processed'); ?>">
                            <i class="fas fa-<?php echo e($delegate->travel_processed ? 'undo' : 'check'); ?>"></i> 
                            <?php echo e($delegate->travel_processed ? 'Unmark Processed' : 'Mark Processed'); ?>

                        </button>
                        <?php endif; ?>
                        
                        <?php if(auth('admin')->user()->role === 'admin'): ?>
                        <button type="button"
                                onclick="openTravelProcessedModal(<?php echo e($delegate->id); ?>, '<?php echo e(addslashes($delegate->user->full_name)); ?>', <?php echo e($delegate->travel_processed ? 'true' : 'false'); ?>)"
                                class="ml-3 text-<?php echo e($delegate->travel_processed ? 'orange' : 'green'); ?>-600 hover:text-<?php echo e($delegate->travel_processed ? 'orange' : 'green'); ?>-900"
                                title="<?php echo e($delegate->travel_processed ? 'Mark as Unprocessed' : 'Mark as Processed'); ?>">
                            <i class="fas fa-<?php echo e($delegate->travel_processed ? 'undo' : 'check'); ?>"></i> 
                            <?php echo e($delegate->travel_processed ? 'Unmark Processed' : 'Mark Processed'); ?>

                        </button>
                        <button type="button" 
                                onclick="cancelApproval(<?php echo e($delegate->id); ?>, '<?php echo e(addslashes($delegate->user->full_name)); ?>')" 
                                class="ml-3 text-red-600 hover:text-red-900"
                                title="Cancel Approval">
                            <i class="fas fa-times-circle"></i> Cancel Approval
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="<?php echo e(auth('admin')->user()->role === 'travels' ? '11' : '10'); ?>" class="px-6 py-4 text-center text-gray-500">
                        No approved delegates found
                        <?php if(request()->hasAny(['search', 'delegate_category', 'country', 'travel_processed'])): ?>
                            matching your filters
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <div class="p-6">
        <?php echo e($delegates->appends(request()->query())->links()); ?>

    </div>
</div>

<!-- Include PDF Preview Modal -->
<?php echo $__env->make('components.invitation-preview-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- Include Mark Travel Processed Modal -->
<?php echo $__env->make('components.mark-travel-processed-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- Include Passport Preview Modal (Admin and Travels roles) -->
<?php if(in_array(auth('admin')->user()->role, ['admin', 'travels'])): ?>
    <?php echo $__env->make('components.passport-preview-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>

<script>
function requestPassportEmail(delegateId, delegateName) {
    if (confirm(`Send passport request email to ${delegateName}?`)) {
        // Create a form to submit the request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?php echo e(url('/approved-delegates/${delegateId}/request-passport')); ?>`;
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '<?php echo e(csrf_token()); ?>';
        form.appendChild(csrfToken);
        
        // Add to body and submit
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
}

function cancelApproval(delegateId, delegateName) {
    if (confirm(`Are you sure you want to cancel the approval for ${delegateName}?\n\nThis will move them back to pending status and they will need to be re-reviewed.`)) {
        // Create a form to submit the request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?php echo e(url('delegates')); ?>/${delegateId}/reset-to-pending`;
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '<?php echo e(csrf_token()); ?>';
        form.appendChild(csrfToken);
        
        // Add to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/approved-delegates/index.blade.php ENDPATH**/ ?>