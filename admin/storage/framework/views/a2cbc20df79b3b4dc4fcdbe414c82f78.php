<?php $__env->startSection('title', 'Registration Details'); ?>
<?php $__env->startSection('page-title', 'Registration #' . $registration->id); ?>

<?php $__env->startSection('content'); ?>
<?php
    $isDelegate = $registration->package_id == config('app.delegate_package_id');
    $canReceiveInvitation = $registration->isPaid() || ($isDelegate && $registration->status === 'approved');
?>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- User Information -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">User Information</h3>
        <dl class="space-y-3">
            <div>
                <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                <dd class="text-sm text-gray-900"><?php echo e($registration->user->title); ?> <?php echo e($registration->user->full_name); ?></dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Email</dt>
                <dd class="text-sm text-gray-900"><?php echo e($registration->user->email); ?></dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                <dd class="text-sm text-gray-900"><?php echo e($registration->user->phone ?? 'N/A'); ?></dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Country</dt>
                <dd class="text-sm text-gray-900"><?php echo e($registration->user->country ?? 'N/A'); ?></dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Nationality</dt>
                <dd class="text-sm text-gray-900"><?php echo e($registration->user->nationality ?? 'N/A'); ?></dd>
            </div>
            <?php if($registration->user->national_id): ?>
            <div>
                <dt class="text-sm font-medium text-gray-500">National ID</dt>
                <dd class="text-sm text-gray-900"><?php echo e($registration->user->national_id); ?></dd>
            </div>
            <?php endif; ?>
            <div>
                <dt class="text-sm font-medium text-gray-500">Organization</dt>
                <dd class="text-sm text-gray-900"><?php echo e($registration->user->organization ?? 'N/A'); ?></dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Position</dt>
                <dd class="text-sm text-gray-900"><?php echo e($registration->user->position ?? 'N/A'); ?></dd>
            </div>
            <?php if($isDelegate && $registration->user->delegate_category): ?>
            <div>
                <dt class="text-sm font-medium text-gray-500">Delegate Category</dt>
                <dd class="text-sm text-gray-900">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                        <?php echo e($registration->user->delegate_category); ?>

                    </span>
                </dd>
            </div>
            <?php endif; ?>
            <?php if($registration->user->requires_visa !== null): ?>
            <div>
                <dt class="text-sm font-medium text-gray-500">Requires Visa</dt>
                <dd class="text-sm text-gray-900">
                    <?php if($registration->user->requires_visa): ?>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                            <i class="fas fa-check"></i> Yes
                        </span>
                    <?php else: ?>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            <i class="fas fa-times"></i> No
                        </span>
                    <?php endif; ?>
                </dd>
            </div>
            <?php endif; ?>
        </dl>
    </div>

    <!-- Contact & Billing Address Information -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Contact & Billing Address Details</h3>
        <dl class="space-y-3">
            <?php if($registration->user->address_line1): ?>
            <div>
                <dt class="text-sm font-medium text-gray-500">Address Line 1</dt>
                <dd class="text-sm text-gray-900"><?php echo e($registration->user->address_line1); ?></dd>
            </div>
            <?php endif; ?>
            <?php if($registration->user->address_line2): ?>
            <div>
                <dt class="text-sm font-medium text-gray-500">Address Line 2</dt>
                <dd class="text-sm text-gray-900"><?php echo e($registration->user->address_line2); ?></dd>
            </div>
            <?php endif; ?>
            <?php if($registration->user->city): ?>
            <div>
                <dt class="text-sm font-medium text-gray-500">City</dt>
                <dd class="text-sm text-gray-900"><?php echo e($registration->user->city); ?></dd>
            </div>
            <?php endif; ?>
            <?php if($registration->user->state): ?>
            <div>
                <dt class="text-sm font-medium text-gray-500">State/Province</dt>
                <dd class="text-sm text-gray-900"><?php echo e($registration->user->state); ?></dd>
            </div>
            <?php endif; ?>
            <?php if($registration->user->postal_code): ?>
            <div>
                <dt class="text-sm font-medium text-gray-500">Postal Code</dt>
                <dd class="text-sm text-gray-900"><?php echo e($registration->user->postal_code); ?></dd>
            </div>
            <?php endif; ?>
            <?php if($registration->user->institution): ?>
            <div>
                <dt class="text-sm font-medium text-gray-500">Institution</dt>
                <dd class="text-sm text-gray-900"><?php echo e($registration->user->institution); ?></dd>
            </div>
            <?php endif; ?>
        </dl>
    </div>
</div>

<!-- Registration Details -->
<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Registration Details</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div>
            <dt class="text-sm font-medium text-gray-500">Package</dt>
            <dd class="text-sm text-gray-900 mt-1">
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                    <?php echo e($registration->package->name); ?>

                </span>
            </dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Registration Type</dt>
            <dd class="text-sm text-gray-900 mt-1"><?php echo e(ucfirst($registration->registration_type)); ?></dd>
        </div>
        <?php if(auth('admin')->user()->role !== 'hosts'): ?>
        <div>
            <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
            <dd class="text-sm text-gray-900 mt-1 font-semibold text-lg">$<?php echo e(number_format($registration->total_amount, 2)); ?> <?php echo e($registration->currency); ?></dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Payment Status</dt>
            <dd class="text-sm mt-1">
                <?php if($registration->isPaid()): ?>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        <i class="fas fa-check-circle"></i> Paid
                    </span>
                <?php else: ?>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        <i class="fas fa-clock"></i> Pending
                    </span>
                <?php endif; ?>
            </dd>
        </div>
        <?php endif; ?>
        <?php if($isDelegate): ?>
        <div>
            <dt class="text-sm font-medium text-gray-500">Delegate Status</dt>
            <dd class="text-sm mt-1">
                <?php if($registration->status === 'approved'): ?>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        <i class="fas fa-check mr-1"></i>Approved
                    </span>
                <?php elseif($registration->status === 'pending'): ?>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        <i class="fas fa-clock mr-1"></i>Pending Review
                    </span>
                <?php elseif($registration->status === 'rejected'): ?>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                        <i class="fas fa-times mr-1"></i>Rejected
                    </span>
                <?php endif; ?>
            </dd>
        </div>
        <?php if($registration->rejection_reason): ?>
        <div class="col-span-full">
            <dt class="text-sm font-medium text-gray-500">Rejection Reason</dt>
            <dd class="text-sm text-gray-900 mt-1 p-3 bg-red-50 border border-red-200 rounded-lg">
                <i class="fas fa-info-circle text-red-600 mr-1"></i>
                <?php echo e($registration->rejection_reason); ?>

            </dd>
        </div>
        <?php endif; ?>
        <?php if($registration->travel_processed !== null): ?>
        <div>
            <dt class="text-sm font-medium text-gray-500">Travel Processed</dt>
            <dd class="text-sm mt-1">
                <?php if($registration->travel_processed): ?>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        <i class="fas fa-check-circle"></i> Processed
                    </span>
                <?php else: ?>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        <i class="fas fa-clock"></i> Pending
                    </span>
                <?php endif; ?>
            </dd>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <?php if(auth('admin')->user()->role !== 'hosts'): ?>
        <?php if($registration->payment_completed_at): ?>
        <div>
            <dt class="text-sm font-medium text-gray-500">Payment Date</dt>
            <dd class="text-sm text-gray-900 mt-1"><?php echo e($registration->payment_completed_at->format('M d, Y H:i')); ?></dd>
        </div>
        <?php endif; ?>
        <?php if($registration->payment_transaction_id): ?>
        <div>
            <dt class="text-sm font-medium text-gray-500">Transaction ID</dt>
            <dd class="text-sm text-gray-900 mt-1 font-mono"><?php echo e($registration->payment_transaction_id); ?></dd>
        </div>
        <?php endif; ?>
        <?php if($registration->payment_method): ?>
        <div>
            <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
            <dd class="text-sm text-gray-900 mt-1"><?php echo e(ucfirst($registration->payment_method)); ?></dd>
        </div>
        <?php endif; ?>
        <?php if($registration->payment_reference): ?>
        <div>
            <dt class="text-sm font-medium text-gray-500">Payment Reference</dt>
            <dd class="text-sm text-gray-900 mt-1 font-mono"><?php echo e($registration->payment_reference); ?></dd>
        </div>
        <?php endif; ?>
        <?php if($registration->invitation_sent_at): ?>
        <div>
            <dt class="text-sm font-medium text-gray-500">Invitation Sent</dt>
            <dd class="text-sm text-gray-900 mt-1">
                <div><?php echo e($registration->invitation_sent_at->format('M d, Y H:i')); ?></div>
                <?php if($registration->invitationSentBy): ?>
                <div class="text-xs text-gray-500">By: <?php echo e($registration->invitationSentBy->username); ?></div>
                <?php endif; ?>
            </dd>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <div>
            <dt class="text-sm font-medium text-gray-500">Registration Date</dt>
            <dd class="text-sm text-gray-900 mt-1"><?php echo e($registration->created_at->format('M d, Y H:i')); ?></dd>
        </div>
    </div>
</div>

<!-- Passport Information (Admin & Hosts Only) -->
<?php if(in_array(auth('admin')->user()->role, ['admin', 'hosts'])): ?>
<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4 flex items-center">
        <i class="fas fa-passport mr-2 text-indigo-600"></i>
        Passport Information
    </h3>
    
    <dl class="space-y-3">
        <div>
            <dt class="text-sm font-medium text-gray-500">Passport Number</dt>
            <dd class="text-sm text-gray-900"><?php echo e($registration->user->passport_number ?? 'Not provided'); ?></dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Airport of Origin</dt>
            <dd class="text-sm text-gray-900"><?php echo e($registration->user->airport_of_origin ?? 'Not provided'); ?></dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Passport Document</dt>
            <dd class="text-sm text-gray-900">
                <?php if($registration->user->passport_file): ?>
                    <div class="flex flex-col space-y-3">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 w-fit">
                            <i class="fas fa-check-circle"></i> Uploaded
                        </span>
                        <div class="flex space-x-3">
                            <button type="button"
                                    onclick="openPassportPreview('<?php echo e(env('PARENT_APP_URL')); ?>/uploads/passports/<?php echo e($registration->user->passport_file); ?>')"
                                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                <i class="fas fa-eye mr-1"></i> Preview Passport
                            </button>
                            <a href="<?php echo e(env('PARENT_APP_URL')); ?>/uploads/passports/<?php echo e($registration->user->passport_file); ?>"
                               download
                               class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-download mr-1"></i> Download Passport
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        <i class="fas fa-exclamation-triangle"></i> Not uploaded
                    </span>
                <?php endif; ?>
            </dd>
        </div>
    </dl>
</div>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewInvitation', App\Models\Registration::class)): ?>
<?php if($canReceiveInvitation): ?>
<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4 flex items-center">
        <i class="fas fa-envelope mr-2 text-blue-600"></i>
        Invitation Actions
        <?php if($isDelegate && $registration->status === 'approved'): ?>
            <span class="ml-3 px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                <i class="fas fa-info-circle mr-1"></i>Approved Delegate
            </span>
        <?php endif; ?>
    </h3>
    <p class="text-sm text-gray-600 mb-4">
        <?php if($registration->isPaid()): ?>
            Generate and send the official invitation letter for this paid registration.
        <?php elseif($isDelegate && $registration->status === 'approved'): ?>
            Generate and send the official invitation letter for this approved delegate.
        <?php endif; ?>
    </p>
    <div class="flex space-x-4">
        <button type="button" 
                onclick="openPdfModal(<?php echo e($registration->id); ?>)" 
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
            <i class="fas fa-file-pdf"></i> Preview Invitation Letter
        </button>
        <a href="<?php echo e(route('invitations.download', $registration)); ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-download"></i> Download PDF
        </a>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sendInvitation', App\Models\Registration::class)): ?>
        <form method="POST" action="<?php echo e(route('invitations.send')); ?>" class="inline">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="registration_ids[]" value="<?php echo e($registration->id); ?>">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                    onclick="return confirm('Are you sure you want to send the invitation email to <?php echo e($registration->user->email); ?>?')">
                <i class="fas fa-paper-plane"></i> Send Email
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if($registration->isPaid() && in_array(auth('admin')->user()->role, ['admin', 'finance'])): ?>
<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4 flex items-center">
        <i class="fas fa-receipt mr-2 text-green-600"></i>
        Receipt Actions
    </h3>
    <p class="text-sm text-gray-600 mb-4">
        Generate and send the official receipt for this paid registration.
    </p>
    <div class="flex space-x-4">
        <button type="button" 
                onclick="openReceiptModal(<?php echo e($registration->id); ?>)" 
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
            <i class="fas fa-eye"></i> Preview Receipt PDF
        </button>
        <a href="<?php echo e(route('registrations.receipt.download', $registration)); ?>" 
           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            <i class="fas fa-download"></i> Download PDF
        </a>
        <button type="button" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 send-receipt-btn"
                data-registration-id="<?php echo e($registration->id); ?>"
                data-email="<?php echo e($registration->user->email); ?>">
            <i class="fas fa-paper-plane"></i> Send Receipt
        </button>
    </div>
</div>
<?php endif; ?>



<!-- Include PDF Preview Modal -->
<?php echo $__env->make('components.invitation-preview-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- Include Receipt Preview Modal -->
<?php echo $__env->make('components.receipt-preview-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- Include Send Receipt Modal -->
<?php echo $__env->make('components.send-receipt-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- Include Passport Preview Modal (Admin and Hosts roles) -->
<?php if(in_array(auth('admin')->user()->role, ['admin', 'hosts'])): ?>
    <?php echo $__env->make('components.passport-preview-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/registrations/show.blade.php ENDPATH**/ ?>