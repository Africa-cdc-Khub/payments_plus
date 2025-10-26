<?php $__env->startSection('title', 'Registrations'); ?>
<?php $__env->startSection('page-title', 'Registrations'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <!-- Page Title Row -->
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-800">All Registrations</h3>
            <div class="flex gap-3">
                <?php if(auth('admin')->user()->role === 'admin'): ?>
                <button 
                    type="button" 
                    onclick="sendBulkReceipts()"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                    title="Send receipts to all participants with completed payments"
                >
                    <i class="fas fa-receipt"></i> Send Bulk Receipts
                </button>
                <button 
                    type="button" 
                    id="bulkVoidBtn"
                    onclick="voidSelected()"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 hidden"
                >
                    <i class="fas fa-ban"></i> Void Selected (<span id="selectedCount">0</span>)
                </button>
                <?php endif; ?>
            </div>
        </div>
            
        <!-- Filter Form -->
        <form method="GET" class="bg-gray-50 p-4 rounded-lg">
            <!-- Filter Fields in One Row -->
            <div class="flex flex-col sm:flex-row gap-4 mb-4">
                <!-- Registration ID Field -->
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-hashtag mr-1"></i>Registration ID
                    </label>
                    <input 
                        type="text" 
                        name="registration_id" 
                        placeholder="Registration ID..." 
                        value="<?php echo e(request('registration_id')); ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                </div>

                <!-- Search Field -->
                <div class="flex-1">
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

                <!-- Status Filter -->
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-flag mr-1"></i>Status
                    </label>
                    <select 
                        name="status" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                        <option value="">All Status</option>
                        <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="completed" <?php echo e(request('status') === 'completed' ? 'selected' : ''); ?>>Paid</option>
                        <option value="delegates" <?php echo e(request('status') === 'delegates' ? 'selected' : ''); ?>>Delegates</option>
                        <option value="approved_delegates" <?php echo e(request('status') === 'approved_delegates' ? 'selected' : ''); ?>>Approved Delegates</option>
                        <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>>Rejected Delegate</option>
                        <option value="voided" <?php echo e(request('status') === 'voided' ? 'selected' : ''); ?>>Voided</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons in One Row -->
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-2">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-search mr-2"></i>Apply Filters
                </button>
                <?php if(request()->hasAny(['registration_id', 'search', 'status'])): ?>
                <a href="<?php echo e(route('registrations.index')); ?>" class="flex-1 sm:flex-none px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition-colors duration-200 text-sm font-medium text-center">
                    <i class="fas fa-times mr-2"></i>Clear Filters
                </a>
                <?php endif; ?>
                <a href="<?php echo e(route('registrations.export', request()->query())); ?>" class="flex-1 sm:flex-none px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 text-sm font-medium text-center">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </a>
            </div>
        </form>
    </div>

    <div class="p-6">
    <!-- Showing records info and per-page selector -->
    <div class="mb-4 mt-2 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <p class="text-sm text-gray-700 leading-5">
                Showing
                <?php if($registrations->firstItem()): ?>
                    <span class="font-medium"><?php echo e($registrations->firstItem()); ?></span>
                    to
                    <span class="font-medium"><?php echo e($registrations->lastItem()); ?></span>
                <?php else: ?>
                    <?php echo e($registrations->count()); ?>

                <?php endif; ?>
                of
                <span class="font-medium"><?php echo e($registrations->total()); ?></span>
                registrations
            </p>
        
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
                    <table class="w-full min-w-full table-fixed">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="w-12 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <?php if(auth('admin')->user()->role === 'admin'): ?>
                            <th class="w-12 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <input type="checkbox" id="selectAll" onclick="toggleSelectAll()" class="rounded">
                            </th>
                            <?php endif; ?>
                            <th class="w-20 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
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
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'package', 'direction' => request('sort') == 'package' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Package</span>
                                    <?php if(request('sort') == 'package'): ?>
                                        <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort text-xs opacity-50"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="w-24 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'amount', 'direction' => request('sort') == 'amount' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Amount</span>
                                    <?php if(request('sort') == 'amount'): ?>
                                        <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort text-xs opacity-50"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <?php if(!in_array(auth('admin')->user()->role, ['executive'])): ?>
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
                            <th class="w-28 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marked By</th>
                            <th class="w-32 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invitation Sent</th>
                            <?php endif; ?>
                            <th class="w-20 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $registrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $registration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $isDelegate = $registration->package_id == config('app.delegate_package_id');
                            $canReceiveInvitation = $registration->isPaid() || ($isDelegate && $registration->status === 'approved');
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo e($registrations->firstItem() + $index); ?>

                            </td>
                            <?php if(auth('admin')->user()->role === 'admin'): ?>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                    $canVoid = $registration->isPending() 
                                        && !$registration->isVoided() 
                                        && !($isDelegate && $registration->status === 'approved');
                                ?>
                                <?php if($canVoid): ?>
                                <input 
                                    type="checkbox" 
                                    class="registration-checkbox rounded" 
                                    value="<?php echo e($registration->id); ?>"
                                    data-name="<?php echo e($registration->user->full_name); ?>"
                                    onchange="updateBulkVoidButton()"
                                >
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e($registration->id); ?>

                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="text-sm font-medium text-gray-900 break-words"><?php echo e($registration->user->full_name); ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 break-words">
                                <?php echo e($registration->user->email); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo e($registration->package->name); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                $<?php echo e(number_format($registration->total_amount, 2)); ?>

                            </td>
                            <?php if(!in_array(auth('admin')->user()->role, ['executive'])): ?>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($registration->isVoided()): ?>
                                    
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        <i class="fas fa-ban mr-1"></i>Voided
                                    </span>
                                <?php elseif($isDelegate): ?>
                                    
                                    <?php if($registration->status === 'approved'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-user-check mr-1"></i>Approved Delegate
                                        </span>
                                    <?php elseif($registration->status === 'rejected'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-user-times mr-1"></i>Rejected Delegate
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-hourglass-half mr-1"></i>Delegate Pending
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    
                                    <?php if($registration->isPaid()): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Paid
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <?php echo e(($registration->package_id == config('app.delegate_package_id')  ) ? 'N/A' : 'Pending Payment'); ?><i class="fas fa-clock mr-1"></i>
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if($registration->isVoided() && $registration->voidedBy): ?>
                                    <div class="flex items-center" title="Voided by <?php echo e($registration->voidedBy->full_name ?? $registration->voidedBy->username); ?>">
                                        <i class="fas fa-ban text-red-600 mr-1"></i>
                                        <span><?php echo e($registration->voidedBy->username ?? 'Admin'); ?></span>
                                    </div>
                                    <?php if($registration->void_reason): ?>
                                        <div class="text-xs text-gray-400 mt-1" title="<?php echo e($registration->void_reason); ?>">
                                            <i class="fas fa-comment-dots"></i> 
                                            <?php echo e(Str::limit($registration->void_reason, 30)); ?>

                                        </div>
                                    <?php endif; ?>
                                <?php elseif($registration->payment && $registration->payment->completed_by): ?>
                                    <div class="flex items-center" title="Manually marked as paid by <?php echo e($registration->payment->completedBy->full_name ?? $registration->payment->completedBy->username); ?>">
                                        <i class="fas fa-user-check text-green-600 mr-1"></i>
                                        <span><?php echo e($registration->payment->completedBy->username ?? 'Admin'); ?></span>
                                    </div>
                                    <?php if($registration->payment->manual_payment_remarks): ?>
                                        <div class="text-xs text-gray-400 mt-1" title="<?php echo e($registration->payment->manual_payment_remarks); ?>">
                                            <i class="fas fa-comment-dots"></i> 
                                            <?php echo e(Str::limit($registration->payment->manual_payment_remarks, 30)); ?>

                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if($registration->invitation_sent_at): ?>
                                    <div class="flex items-center" title="Invitation sent by <?php echo e($registration->invitationSentBy->full_name ?? $registration->invitationSentBy->username ?? 'Admin'); ?>">
                                        <i class="fas fa-envelope text-blue-600 mr-1"></i>
                                        <div>
                                            <div><?php echo e($registration->invitation_sent_at->format('M d, Y')); ?></div>
                                            <div class="text-xs text-gray-400"><?php echo e($registration->invitationSentBy->username ?? 'Admin'); ?></div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if($registration->isVoided()): ?>
                                    
                                    <a href="<?php echo e(route('registrations.show', $registration)); ?>" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    
                                    <?php if(in_array(auth('admin')->user()->role, ['admin', 'secretariat']) && $registration->registration_type !== 'individual'): ?>
                                    <a href="<?php echo e(route('registration-participants.index', $registration)); ?>" 
                                       class="ml-3 text-red-600 hover:text-red-900"
                                       title="View Participants">
                                        <i class="fas fa-users"></i> Participants
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if(auth('admin')->user()->role === 'admin'): ?>
                                    <button type="button" 
                                            onclick="undoVoid(<?php echo e($registration->id); ?>, '<?php echo e(addslashes($registration->user->full_name)); ?>')" 
                                            class="ml-3 text-green-600 hover:text-green-900"
                                            title="Undo Void">
                                        <i class="fas fa-undo"></i> Undo Void
                                    </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    
                                    <a href="<?php echo e(route('registrations.show', $registration)); ?>" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    
                                    <?php if(in_array(auth('admin')->user()->role, ['admin', 'secretariat']) && $registration->registration_type !== 'individual'): ?>
                                    <a href="<?php echo e(route('registration-participants.index', $registration)); ?>" 
                                       class="ml-3 text-red-600 hover:text-red-900"
                                       title="View Participants">
                                        <i class="fas fa-users"></i> Participants
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('markAsPaid', App\Models\Registration::class)): ?>
                                    <?php if(!$registration->isPaid() && !$isDelegate): ?>
                                    <button type="button" 
                                            onclick="openMarkPaidModal(<?php echo e($registration->id); ?>, '<?php echo e(addslashes($registration->user->full_name)); ?>', '<?php echo e($registration->total_amount); ?>')" 
                                            class="ml-3 text-orange-600 hover:text-orange-900"
                                            title="Mark as Paid">
                                        <i class="fas fa-money-bill-wave"></i> Mark Paid
                                    </button>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewInvitation', App\Models\Registration::class)): ?>
                                    <?php if($canReceiveInvitation): ?>
                                    <button type="button" 
                                            onclick="openPdfModal(<?php echo e($registration->id); ?>)" 
                                            class="ml-3 text-purple-600 hover:text-purple-900"
                                            title="Preview Invitation">
                                        <i class="fas fa-file-pdf"></i> Preview
                                    </button>
                                    <a href="<?php echo e(route('invitations.download', $registration)); ?>" class="ml-3 text-green-600 hover:text-green-900" title="Download Invitation Letter">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if($registration->isPaid() && in_array(auth('admin')->user()->role, ['admin', 'finance'])): ?>
                                    <a href="<?php echo e(route('registrations.invoice', $registration)); ?>" class="ml-3 text-blue-600 hover:text-blue-900" title="Generate Invoice">
                                        <i class="fas fa-file-invoice"></i> Invoice
                                    </a>
                                    
                                    <button type="button" 
                                            onclick="openReceiptModal(<?php echo e($registration->id); ?>)" 
                                            class="ml-3 text-purple-600 hover:text-purple-900"
                                            title="Preview Receipt PDF">
                                        <i class="fas fa-eye"></i> Receipt PDF
                                    </button>
                                    
                                    <a href="<?php echo e(route('registrations.receipt.download', $registration)); ?>" 
                                       class="ml-3 text-green-600 hover:text-green-900" 
                                       title="Download Receipt PDF">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if(auth('admin')->user()->role === 'admin'): ?>
                                    <button type="button" 
                                            onclick="sendInvitationEmail(<?php echo e($registration->id); ?>, '<?php echo e(addslashes($registration->user->full_name)); ?>')" 
                                            class="ml-3 text-indigo-600 hover:text-indigo-900"
                                            title="Send Invitation Email">
                                        <i class="fas fa-envelope"></i> Send Invitation
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if($registration->isPaid() && in_array(auth('admin')->user()->role, ['admin', 'finance'])): ?>
                                    <button type="button" 
                                            class="ml-3 text-green-600 hover:text-green-900 send-receipt-btn"
                                            data-registration-id="<?php echo e($registration->id); ?>"
                                            data-email="<?php echo e($registration->user->email); ?>"
                                            title="Send Receipt PDF">
                                        <i class="fas fa-paper-plane"></i> Send Receipt
                                    </button>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php
                                        $canVoid = auth('admin')->user()->role === 'admin' 
                                            && $registration->isPending() 
                                            && !$registration->isVoided() 
                                            && !($isDelegate && $registration->status === 'approved');
                                    ?>
                                    <?php if($canVoid): ?>
                                    <button type="button" 
                                            onclick="openMarkVoidModal(<?php echo e($registration->id); ?>, '<?php echo e(addslashes($registration->user->full_name)); ?>')" 
                                            class="ml-3 text-red-600 hover:text-red-900"
                                            title="Void Registration">
                                        <i class="fas fa-ban"></i> Void
                                    </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e(auth('admin')->user()->role === 'executive' ? '6' : (auth('admin')->user()->role === 'admin' ? '10' : '9')); ?>" class="px-6 py-4 text-center text-gray-500">No registrations found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>

        <div class="mt-6">
                <?php echo e($registrations->appends(request()->query())->links()); ?>

            </div>
        </div>
    </div>
</div>

<!-- Include PDF Preview Modal -->
<?php echo $__env->make('components.invitation-preview-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- Include Mark Paid Modal -->
<?php echo $__env->make('components.mark-paid-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- Include Mark Void Modal -->
<?php echo $__env->make('components.mark-void-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script>
// Bulk void functions
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.registration-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    updateBulkVoidButton();
}

function updateBulkVoidButton() {
    const checkboxes = document.querySelectorAll('.registration-checkbox:checked');
    const bulkBtn = document.getElementById('bulkVoidBtn');
    const countSpan = document.getElementById('selectedCount');
    
    if (bulkBtn && countSpan) {
        if (checkboxes.length > 0) {
            bulkBtn.classList.remove('hidden');
            countSpan.textContent = checkboxes.length;
            } else {
            bulkBtn.classList.add('hidden');
            countSpan.textContent = '0';
        }
    }
}

function voidSelected() {
    const checkboxes = document.querySelectorAll('.registration-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one registration to void.');
                return;
            }

    const registrationIds = Array.from(checkboxes).map(cb => cb.value);
    openBulkVoidModal(registrationIds);
}
            
function sendInvitationEmail(registrationId, delegateName) {
    if (confirm(`Send invitation email to ${delegateName}?\n\nThis will queue an email with their invitation letter attached.`)) {
        // Create a form to submit the request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?php echo e(url('registrations')); ?>/${registrationId}/send-invitation`;
        
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

function sendReceiptEmail(registrationId, participantName) {
    if (confirm(`Send receipt email to ${participantName}?\n\nThis will queue a receipt email with QR codes and payment confirmation.`)) {
        // Create a form to submit the request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?php echo e(url('registrations')); ?>/${registrationId}/send-receipt`;
        
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

function sendBulkReceipts() {
    if (confirm('Send receipts to ALL participants with completed payments?\n\nThis will queue receipt emails for all paid registrations. This action may take some time to complete.')) {
        // Create a form to submit the request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo e(route("registrations.send-bulk-receipts")); ?>';
        
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

function undoVoid(registrationId, registrantName) {
    if (confirm(`Undo void for ${registrantName}?\n\nThis will restore the registration to pending status.`)) {
        // Create a form to submit the request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?php echo e(url('registrations')); ?>/${registrationId}/undo-void`;
        
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

<!-- Include Receipt Preview Modal -->
<?php echo $__env->make('components.receipt-preview-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- Include Send Receipt Modal -->
<?php echo $__env->make('components.send-receipt-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/registrations/index.blade.php ENDPATH**/ ?>