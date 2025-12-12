<?php $__env->startSection('title', 'Participants'); ?>
<?php $__env->startSection('page-title', 'Participants'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
   
    <!-- Filters and Export -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <!-- Responsive Filter Form -->
            <form method="GET" action="<?php echo e(route('participants.index')); ?>" class="bg-gray-50 p-4 rounded-lg">
                <!-- Mobile: Stack vertically, Desktop: Grid layout -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <!-- Search Field -->
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search mr-1"></i>Search
                        </label>
                        <input 
                            type="text" 
                            id="search" 
                            name="search" 
                            value="<?php echo e(request('search')); ?>"
                            placeholder="Name or email..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                        >
                    </div>

                    <!-- Package Filter -->
                    <div>
                        <label for="package_id" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-box mr-1"></i>Package
                        </label>
                        <select 
                            id="package_id" 
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
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-globe mr-1"></i>Country
                        </label>
                        <select 
                            id="country" 
                            name="country" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                        >
                            <option value="">All Countries</option>
                            <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($country); ?>" <?php echo e(request('country') == $country ? 'selected' : ''); ?>>
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
                    <a href="<?php echo e(route('participants.index')); ?>" class="flex-1 sm:flex-none px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition-colors duration-200 text-sm font-medium text-center">
                        <i class="fas fa-times mr-2"></i>Clear Filters
                    </a>
                    <a href="<?php echo e(route('participants.export', request()->query())); ?>" class="flex-1 sm:flex-none px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 text-sm font-medium text-center">
                        <i class="fas fa-download mr-2"></i>Export CSV
                    </a>
                </div>
            </form>

                <!-- Export Button -->
                <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                    <div class="flex items-center space-x-4">
                        <?php if(request()->hasAny(['search', 'package_id', 'country'])): ?>
                            <div class="flex items-center space-x-2 text-sm text-gray-600">
                                <i class="fas fa-filter"></i>
                                <span>Filtered by: </span>
                                <?php if(request('search')): ?>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                        Search: "<?php echo e(request('search')); ?>"
                                    </span>
                                <?php endif; ?>
                                <?php if(request('package_id')): ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                        Package: <?php echo e($packages->find(request('package_id'))->name ?? 'Unknown'); ?>

                                    </span>
                                <?php endif; ?>
                                <?php if(request('country')): ?>
                                    <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">
                                        Country: <?php echo e(request('country')); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <a 
                        href="<?php echo e(route('participants.export', request()->query())); ?>" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                        <i class="fas fa-download mr-2"></i> Export CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Participants Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden mt-2">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h3 class="text-lg font-medium text-gray-900">All Participants</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Showing <?php echo e($registrations->firstItem() ?? 0); ?> to <?php echo e($registrations->lastItem() ?? 0); ?> of <?php echo e($registrations->total()); ?> registrations
                    <span class="text-gray-700 font-medium">(<?php echo e($totalParticipants); ?> total people including group members)</span>
                </p>
            </div>
            
            <!-- Per-page selector -->
            <?php if (isset($component)) { $__componentOriginal720c5d99204acad589a79c73de989541 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal720c5d99204acad589a79c73de989541 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.per-page-selector','data' => ['paginator' => $registrations,'currentPerPage' => request('per_page', 50)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('per-page-selector'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($registrations),'current-per-page' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('per_page', 50))]); ?>
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
                <table class="w-full min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Country</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package</th>
                         <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delegate Category</th>
                        <?php if(in_array(auth('admin')->user()->role, ['admin', 'hosts','travels'])): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nationality</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Passport</th>
                        <?php endif; ?>
                        <?php if(!in_array(auth('admin')->user()->role, ['executive'])): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                                <span>Status</span>
                                <?php if(request('sort') == 'status'): ?>
                                    <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                                <?php else: ?>
                                    <i class="fas fa-sort text-xs opacity-50"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <?php endif; ?>
                        <?php if(!in_array(auth('admin')->user()->role, ['executive', 'hosts'])): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                        <?php endif; ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Registered</th>
                        <?php if(in_array(auth('admin')->user()->role, ['admin','travels'])): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $registrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $registration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    
                    <?php
                        $isDelegate = $registration->status === 'approved';
                        $type = $isDelegate ? 'Delegate' : 'Paid Participant';
                    ?>
                    
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo e($registrations->firstItem() + $index); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            #<?php echo e($registration->id); ?>

                            <?php if($registration->participants->count() > 0): ?>
                                <span class="ml-1 p-1 text-xs font-semibold rounded bg-green-100 text-green-800" title="Group registration">
                                    +<?php echo e($registration->participants->count()); ?>

                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?php echo e($registration->user->full_name ?? '-'); ?>

                            <span class="ml-1 text-xs text-gray-500">(Primary)</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo e($registration->user->email ?? '-'); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo e($registration->user->phone ?? '-'); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo e($registration->user->country ?? '-'); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                <?php echo e($registration->package->name ?? '-'); ?>

                            </span>
                        </td>
                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo e($registration->user->delegate_category ?? '-'); ?>

                        </td>
                        <?php if(in_array(auth('admin')->user()->role, ['admin', 'hosts','travels'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo e($registration->user->nationality ?? '-'); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex items-center space-x-2">
                                <span><?php echo e($registration->user->passport_number ?? '-'); ?></span>
                                <?php if($registration->user->passport_file): ?>
                                    <button type="button" 
                                            onclick="openPassportPreview('<?php echo e(env('PARENT_APP_URL')); ?>/uploads/passports/<?php echo e($registration->user->passport_file); ?>')"
                                            class="text-blue-600 hover:text-blue-900 font-medium">
                                        <i class="fas fa-eye"></i> Preview
                                    </button>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs">No document</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                        <?php if(!in_array(auth('admin')->user()->role, ['executive'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if($registration->status === 'approved'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle"></i> Approved
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <?php echo e(ucfirst($registration->status)); ?>

                                </span>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        <?php if(!in_array(auth('admin')->user()->role, ['executive', 'hosts'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if($registration->payment_status === 'completed'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle"></i> Paid
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <?php echo e(ucfirst($registration->payment_status)); ?>

                                </span>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                <i class="fas fa-user-tie"></i> <?php echo e($type); ?>

                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo e($registration->created_at ? $registration->created_at->format('M d, Y') : '-'); ?>

                        </td>
                        <?php if(in_array(auth('admin')->user()->role, ['admin', 'hosts','travels'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex flex-wrap gap-3">
                                <a href="<?php echo e(route('registrations.show', $registration)); ?>" class="text-blue-600 hover:text-blue-900 px-2 py-1 rounded hover:bg-blue-50">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <button type="button" 
                                        onclick="openPdfModal(<?php echo e($registration->id); ?>)"
                                        class="text-green-600 hover:text-green-900 px-2 py-1 rounded hover:bg-green-50">
                                    <i class="fas fa-envelope"></i> Preview Invitation
                                </button>
                                <?php if($registration->invitation_sent_at): ?>
                                    <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded">
                                        <i class="fas fa-check"></i> Sent
                                    </span>
                                <?php endif; ?>
                                <?php
                                    $isDelegate = $registration->package_id == config('app.delegate_package_id');
                                    $canReceiveCertificate = $registration->payment_status === 'completed' || ($isDelegate && $registration->status === 'approved');
                                ?>
                                <?php if($canReceiveCertificate): ?>
                                <button type="button" 
                                        onclick="previewCertificate(<?php echo e($registration->id); ?>, null)"
                                        class="text-indigo-600 hover:text-indigo-900 px-2 py-1 rounded hover:bg-indigo-50"
                                        title="Preview Certificate">
                                    <i class="fas fa-certificate"></i> Preview Certificate
                                </button>
                                <a href="<?php echo e(route('certificates.download')); ?>?registration_id=<?php echo e($registration->id); ?>" 
                                   class="text-purple-600 hover:text-purple-900 px-2 py-1 rounded hover:bg-purple-50"
                                   title="Download Certificate">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <button type="button" 
                                        onclick="sendCertificate(<?php echo e($registration->id); ?>, null, '<?php echo e(addslashes($registration->user->full_name)); ?>')"
                                        class="text-orange-600 hover:text-orange-900 px-2 py-1 rounded hover:bg-orange-50"
                                        title="Send Certificate">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    
                    
                    <?php $__currentLoopData = $registration->participants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupMember): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50 bg-gray-25">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                            <i class="fas fa-arrow-turn-down-right ml-2"></i> #<?php echo e($registration->id); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <span class="ml-4"><?php echo e($groupMember->full_name); ?></span>
                            <span class="ml-1 text-xs text-gray-500">(Member)</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo e($groupMember->email ?? '-'); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo e($groupMember->phone ?? '-'); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo e($groupMember->country ?? '-'); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                <?php echo e($registration->package->name ?? '-'); ?>

                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo e($groupMember->delegate_category ?? '-'); ?>

                        </td>
                        <?php if(in_array(auth('admin')->user()->role, ['admin', 'hosts','travels'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo e($groupMember->nationality ?? '-'); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex items-center space-x-2">
                                <span><?php echo e($groupMember->passport_number ?? '-'); ?></span>
                                <?php if($groupMember->passport_file): ?>
                                    <button type="button" 
                                            onclick="openPassportPreview('<?php echo e(env('PARENT_APP_URL')); ?>/uploads/passports/<?php echo e($groupMember->passport_file); ?>')"
                                            class="text-blue-600 hover:text-blue-900 font-medium">
                                        <i class="fas fa-eye"></i> Preview
                                    </button>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs">No document</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                        <?php if(!in_array(auth('admin')->user()->role, ['executive'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                Group Member
                            </span>
                        </td>
                        <?php endif; ?>
                        <?php if(!in_array(auth('admin')->user()->role, ['executive', 'hosts'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if($registration->payment_status === 'completed'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle"></i> Paid
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <?php echo e(ucfirst($registration->payment_status)); ?>

                                </span>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                <i class="fas fa-users"></i> Group Member
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo e($registration->created_at ? $registration->created_at->format('M d, Y') : '-'); ?>

                        </td>
                        <?php if(in_array(auth('admin')->user()->role, ['admin', 'hosts','travels'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex flex-wrap gap-3">
                                <a href="<?php echo e(route('registrations.show', $registration)); ?>" class="text-blue-600 hover:text-blue-900 px-2 py-1 rounded hover:bg-blue-50">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <button type="button" 
                                        onclick="openPdfModal(<?php echo e($registration->id); ?>, <?php echo e($groupMember->id); ?>)"
                                        class="text-green-600 hover:text-green-900 px-2 py-1 rounded hover:bg-green-50">
                                    <i class="fas fa-envelope"></i> Preview Invitation
                                </button>
                                <?php if($groupMember->invitation_sent_at): ?>
                                    <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded">
                                        <i class="fas fa-check"></i> Sent
                                    </span>
                                <?php endif; ?>
                                <?php
                                    $isDelegate = $registration->package_id == config('app.delegate_package_id');
                                    $canReceiveCertificate = $registration->payment_status === 'completed' || ($isDelegate && $registration->status === 'approved');
                                ?>
                                <?php if($canReceiveCertificate): ?>
                                <button type="button" 
                                        onclick="previewCertificate(<?php echo e($registration->id); ?>, <?php echo e($groupMember->id); ?>)"
                                        class="text-indigo-600 hover:text-indigo-900 px-2 py-1 rounded hover:bg-indigo-50"
                                        title="Preview Certificate">
                                    <i class="fas fa-certificate"></i> Preview Certificate
                                </button>
                                <a href="<?php echo e(route('certificates.download')); ?>?registration_id=<?php echo e($registration->id); ?>&participant_id=<?php echo e($groupMember->id); ?>" 
                                   class="text-purple-600 hover:text-purple-900 px-2 py-1 rounded hover:bg-purple-50"
                                   title="Download Certificate">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <button type="button" 
                                        onclick="sendCertificate(<?php echo e($registration->id); ?>, <?php echo e($groupMember->id); ?>, '<?php echo e(addslashes($groupMember->full_name)); ?>')"
                                        class="text-orange-600 hover:text-orange-900 px-2 py-1 rounded hover:bg-orange-50"
                                        title="Send Certificate">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <?php
                            $colspan = 9; // Base columns
                            if (!in_array(auth('admin')->user()->role, ['executive'])) {
                                $colspan += 1; // Status column
                            }
                            if (!in_array(auth('admin')->user()->role, ['executive', 'hosts'])) {
                                $colspan += 1; // Payment column
                            }
                            if (in_array(auth('admin')->user()->role, ['admin', 'hosts','travels'])) {
                                $colspan += 3; // Actions column + Nationality + Passport columns
                            }
                        ?>
                        <td colspan="<?php echo e($colspan); ?>" class="px-6 py-4 text-center text-gray-500">
                            No participants found
                            <?php if(request()->hasAny(['search', 'package_id', 'country'])): ?>
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
            <?php echo e($registrations->appends(request()->query())->links('vendor.pagination.always-show-numbers')); ?>

        </div>
    </div>
</div>


<!-- Include PDF Preview Modal -->
<?php echo $__env->make('components.invitation-preview-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- Include Passport Preview Modal (Admin and Hosts roles) -->
<?php if(in_array(auth('admin')->user()->role, ['admin', 'hosts'])): ?>
    <?php echo $__env->make('components.passport-preview-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>

<script>
function previewCertificate(registrationId, participantId) {
    let url = '<?php echo e(route("certificates.preview")); ?>?registration_id=' + registrationId;
    if (participantId) {
        url += '&participant_id=' + participantId;
    }
    window.open(url, '_blank');
}

function sendCertificate(registrationId, participantId, participantName) {
    if (confirm(`Send certificate to ${participantName}?\n\nThis will queue a certificate email with PDF attachment.`)) {
        // Create a form to submit the request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo e(route("certificates.send")); ?>';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '<?php echo e(csrf_token()); ?>';
        form.appendChild(csrfToken);
        
        // Add registration_id
        const regInput = document.createElement('input');
        regInput.type = 'hidden';
        regInput.name = 'registration_id';
        regInput.value = registrationId;
        form.appendChild(regInput);
        
        // Add participant_id if provided
        if (participantId) {
            const partInput = document.createElement('input');
            partInput.type = 'hidden';
            partInput.name = 'participant_id';
            partInput.value = participantId;
            form.appendChild(partInput);
        }
        
        // Add to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/participants/index.blade.php ENDPATH**/ ?>