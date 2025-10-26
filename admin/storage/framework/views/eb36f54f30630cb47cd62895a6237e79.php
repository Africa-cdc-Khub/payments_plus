<?php $__env->startSection('title', 'Invoices'); ?>
<?php $__env->startSection('page-title', 'Invoice Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Invoice Management</h3>
            
            <div class="flex space-x-2">
                <a href="<?php echo e(route('invoices.export', request()->query())); ?>" 
                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center space-x-2">
                    <i class="fas fa-download"></i>
                    <span>Export CSV</span>
                </a>
                <button type="button" onclick="openCreateInvoiceModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus"></i> Create Invoice
                </button>
            </div>
        </div>

        <!-- Responsive Filter Form -->
        <form method="GET" class="bg-gray-50 p-4 rounded-lg mb-4">
            <!-- Mobile: Stack vertically, Desktop: Grid layout -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <!-- Search Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1"></i>Search
                    </label>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Invoice number, biller name or email..." 
                        value="<?php echo e(request('search')); ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-flag mr-1"></i>Status
                    </label>
                    <select 
                        name="status" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                        <option value="">All Status</option>
                        <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="paid" <?php echo e(request('status') === 'paid' ? 'selected' : ''); ?>>Paid</option>
                        <option value="cancelled" <?php echo e(request('status') === 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons - Responsive Layout -->
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-2">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-search mr-2"></i>Apply Filters
                </button>
                <a href="<?php echo e(route('invoices.index')); ?>" class="flex-1 sm:flex-none px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition-colors duration-200 text-sm font-medium text-center">
                    <i class="fas fa-times mr-2"></i>Clear Filters
                </a>
                <a href="<?php echo e(route('invoices.export', request()->query())); ?>" class="flex-1 sm:flex-none px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 text-sm font-medium text-center">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </a>
            </div>
        </form>

        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-clock text-yellow-600 text-2xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-yellow-900">Pending Invoices</h4>
                        <p class="text-2xl font-bold text-yellow-700"><?php echo e($invoices->where('status', 'pending')->count()); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-green-900">Paid Invoices</h4>
                        <p class="text-2xl font-bold text-green-700"><?php echo e($invoices->where('status', 'paid')->count()); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-times-circle text-red-600 text-2xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-red-900">Cancelled Invoices</h4>
                        <p class="text-2xl font-bold text-red-700"><?php echo e($invoices->where('status', 'cancelled')->count()); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Showing records info and per-page selector -->
    <div class="mb-4 mt-2 px-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <p class="text-sm text-gray-700 leading-5">
            Showing
            <?php if($invoices->firstItem()): ?>
                <span class="font-medium"><?php echo e($invoices->firstItem()); ?></span>
                to
                <span class="font-medium"><?php echo e($invoices->lastItem()); ?></span>
            <?php else: ?>
                <?php echo e($invoices->count()); ?>

            <?php endif; ?>
            of
            <span class="font-medium"><?php echo e($invoices->total()); ?></span>
            invoices
        </p>
        
        <!-- Per-page selector -->
        <?php if (isset($component)) { $__componentOriginal720c5d99204acad589a79c73de989541 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal720c5d99204acad589a79c73de989541 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.per-page-selector','data' => ['paginator' => $invoices,'currentPerPage' => request('per_page', 50)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('per-page-selector'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoices),'current-per-page' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('per_page', 50))]); ?>
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
            <table class="w-full min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'invoice_number', 'direction' => request('sort') == 'invoice_number' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Invoice #</span>
                            <?php if(request('sort') == 'invoice_number'): ?>
                                <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                            <?php else: ?>
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'biller_name', 'direction' => request('sort') == 'biller_name' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Biller</span>
                            <?php if(request('sort') == 'biller_name'): ?>
                                <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                            <?php else: ?>
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Status</span>
                            <?php if(request('sort') == 'status'): ?>
                                <i class="fas fa-sort-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> text-xs"></i>
                            <?php else: ?>
                                <i class="fas fa-sort text-xs opacity-50"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Created</span>
                            <?php if(request('sort') == 'created_at'): ?>
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
                <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo e($invoices->firstItem() + $index); ?>

                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php echo e($invoice->invoice_number); ?>

                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900"><?php echo e($invoice->biller_name); ?></div>
                        <div class="text-sm text-gray-500"><?php echo e($invoice->biller_email); ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php echo e($invoice->item); ?>

                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php echo e($invoice->formatted_amount); ?>

                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if($invoice->status === 'pending'): ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i>Pending
                            </span>
                        <?php elseif($invoice->status === 'paid'): ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check mr-1"></i>Paid
                            </span>
                        <?php elseif($invoice->status === 'cancelled'): ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-times mr-1"></i>Cancelled
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo e($invoice->created_at ? $invoice->created_at->format('M d, Y') : 'N/A'); ?>

                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="<?php echo e(route('invoices.show', $invoice)); ?>" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye"></i> View
                        </a>
                        
                        <button type="button" 
                                onclick="openInvoiceModal(<?php echo e($invoice->id); ?>)" 
                                class="ml-3 text-purple-600 hover:text-purple-900"
                                title="Preview PDF">
                            <i class="fas fa-eye"></i> PDF
                        </button>
                        
                        <a href="<?php echo e(route('invoices.email-preview', $invoice)); ?>" 
                           target="_blank"
                           class="ml-3 text-indigo-600 hover:text-indigo-900"
                           title="Preview Email">
                            <i class="fas fa-envelope"></i> Email
                        </a>
                        
                        <a href="<?php echo e(route('invoices.download', $invoice)); ?>" class="ml-3 text-green-600 hover:text-green-900">
                            <i class="fas fa-download"></i> Download
                        </a>

                        <button type="button"
                                class="ml-3 text-blue-600 hover:text-blue-900 send-invoice-btn"
                                data-invoice-id="<?php echo e($invoice->id); ?>"
                                data-email="<?php echo e($invoice->biller_email); ?>">
                            <i class="fas fa-paper-plane"></i> Send
                        </button>
                        
                        <?php if($invoice->status === 'paid'): ?>
                        <button type="button" 
                                onclick="openInvoiceReceiptModal(<?php echo e($invoice->id); ?>)" 
                                class="ml-3 text-green-600 hover:text-green-900"
                                title="Preview Receipt PDF">
                            <i class="fas fa-receipt"></i> Receipt
                        </button>
                        
                        <a href="<?php echo e(route('invoices.receipt.download', $invoice)); ?>" class="ml-3 text-green-600 hover:text-green-900">
                            <i class="fas fa-download"></i> Receipt PDF
                        </a>

                        <button type="button" 
                                class="ml-3 text-green-600 hover:text-green-900 send-invoice-receipt-btn"
                                data-invoice-id="<?php echo e($invoice->id); ?>"
                                data-email="<?php echo e($invoice->biller_email); ?>">
                            <i class="fas fa-paper-plane"></i> Send Receipt
                        </button>
                        <?php endif; ?>
                        
                        <?php if($invoice->status === 'pending'): ?>
                            <button type="button" 
                                    class="ml-3 text-orange-600 hover:text-orange-900 edit-invoice-btn"
                                    title="Edit Invoice"
                                    data-invoice-id="<?php echo e($invoice->id); ?>"
                                    data-biller-name="<?php echo e($invoice->biller_name); ?>"
                                    data-biller-email="<?php echo e($invoice->biller_email); ?>"
                                    data-biller-address="<?php echo e($invoice->biller_address); ?>"
                                    data-item="<?php echo e($invoice->item); ?>"
                                    data-description="<?php echo e($invoice->description); ?>"
                                    data-quantity="<?php echo e($invoice->quantity); ?>"
                                    data-rate="<?php echo e($invoice->rate); ?>"
                                    data-currency="<?php echo e($invoice->currency); ?>"
                                    data-status="<?php echo e($invoice->status); ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            
                            <button type="button" 
                                    onclick="markAsPaid(<?php echo e($invoice->id); ?>, '<?php echo e(addslashes($invoice->biller_name)); ?>')" 
                                    class="ml-3 text-green-600 hover:text-green-900"
                                    title="Mark as Paid">
                                <i class="fas fa-check-circle"></i> Mark Paid
                            </button>
                            
                            <button type="button" 
                                    onclick="cancelInvoice(<?php echo e($invoice->id); ?>, '<?php echo e(addslashes($invoice->biller_name)); ?>')" 
                                    class="ml-3 text-red-600 hover:text-red-900"
                                    title="Cancel Invoice">
                                <i class="fas fa-times-circle"></i> Cancel
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        No invoices found
                        <?php if(request()->hasAny(['search', 'status'])): ?>
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
        <?php echo e($invoices->appends(request()->query())->links()); ?>

    </div>
</div>

<script>
function markAsPaid(invoiceId, billerName) {
    if (confirm(`Are you sure you want to mark invoice for ${billerName} as paid?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?php echo e(url('invoices')); ?>/${invoiceId}/mark-paid`;
        
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = '<?php echo e(csrf_token()); ?>';
        form.appendChild(token);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function cancelInvoice(invoiceId, billerName) {
    if (confirm(`Are you sure you want to cancel invoice for ${billerName}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?php echo e(url('invoices')); ?>/${invoiceId}/cancel`;
        
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = '<?php echo e(csrf_token()); ?>';
        form.appendChild(token);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<!-- Include Invoice Preview Modal -->
<?php echo $__env->make('components.invoice-preview-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- Include Create Invoice Modal -->
<?php echo $__env->make('components.create-invoice-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- Include Edit Invoice Modal -->
<?php echo $__env->make('components.edit-invoice-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- Include Send Invoice Modal -->
<?php echo $__env->make('components.send-invoice-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- Include Invoice Receipt Preview Modal -->
<?php echo $__env->make('components.invoice-receipt-preview-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- Include Send Invoice Receipt Modal -->
<?php echo $__env->make('components.send-invoice-receipt-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/invoices/index.blade.php ENDPATH**/ ?>