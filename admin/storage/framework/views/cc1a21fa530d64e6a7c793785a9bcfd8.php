<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - CPHIA 2025</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: 'Open Sans', 'Helvetica', 'Arial', sans-serif;
            background-color: #FFFFFF;
            color: #1F2937;
            margin: 0;
            padding: 0;
            font-size: 11pt;
        }
        .container {
            width: 100%;
            padding: 0;
        }
        .content-wrapper {
            max-width: 170mm;
            margin: 0 auto;
            padding: 0 20mm;
        }
        .header {
            width: 100%;
            margin-bottom: 15mm;
            padding: 0;
            text-align: center;
        }

        .header img {
            width: 100%;
            height: auto;
            display: block;
        }

        .invoice-details {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .bill-to {
            display: table-cell;
            width: 60%;
            vertical-align: top;
            padding-right: 20px;
        }
        .invoice-info {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: left;
        }
        .bill-to h3, .invoice-info h3 {
            font-size: 12pt;
            font-weight: bold;
            color: #374151;
            margin: 0 0 10px 0;
        }
        .bill-to p, .invoice-info p {
            margin: 2px 0;
            font-size: 10pt;
        }
        .invoice-info p strong {
            font-weight: bold;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #FFFFFF;
        }
        .items-table th {
            background-color: #F9FAFB;
            border: 1px solid #E5E7EB;
            padding: 8px 12px;
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
            color: #374151;
        }
        .items-table td {
            border: 1px solid #E5E7EB;
            padding: 8px 12px;
            font-size: 10pt;
            vertical-align: top;
        }
        .items-table .number-col {
            width: 5%;
            text-align: center;
        }
        .items-table .item-col {
            width: 15%;
        }
        .items-table .description-col {
            width: 45%;
        }
        .items-table .qty-col {
            width: 8%;
            text-align: center;
        }
        .items-table .rate-col {
            width: 12%;
            text-align: right;
        }
        .items-table .amount-col {
            width: 15%;
            text-align: right;
        }
        .total-row {
            background-color: #F9FAFB;
            font-weight: bold;
        }
        .total-row td {
            border-top: 2px solid #374151;
        }
        .bank-details {
            margin-top: 30px;
            padding: 15px;
            background-color: #F9FAFB;
            border: 1px solid #E5E7EB;
        }
        .bank-details h3 {
            font-size: 12pt;
            font-weight: bold;
            color: #374151;
            margin: 0 0 10px 0;
        }
        .bank-details p {
            margin: 2px 0;
            font-size: 10pt;
        }
        .footer {
            width: 100%;
            text-align: center;
            margin-top: 20px;
            padding: 0;
            bottom: 0;
            position: absolute;
            margin-bottom: 20mm;
        }
        .footer img {
            width: auto;
            max-height: 20mm;
            height: auto;
            display: block;
        }

        .invoice-info h3 .info-label{
            margin-left: 12px;
            display: inline-block;
            font-weight: normal;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <img src="<?php echo e(public_path('images/invoice-top.png')); ?>" alt="CPHIA 2025 Invoice Header" />
        </header>

        <div class="content-wrapper">
            <div class="invoice-details">
                <div class="bill-to">
                    <h3>Bill To</h3>
                    <p><strong><?php echo e($user->first_name); ?> <?php echo e($user->last_name); ?></strong></p>
                    <p><?php echo e($user->email); ?></p>
                    <?php if($user->organization): ?>
                        <p><?php echo e($user->organization); ?></p>
                    <?php endif; ?>
                    <?php if($user->institution): ?>
                        <p><?php echo e($user->institution); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="invoice-info">
                    <h3>Invoice <div class="info-label">REG-<?php echo e($registration->id); ?></div></h3>
                    <h3>Registration Date: <div class="info-label"><?php echo e($registration->created_at->format('d/m/Y')); ?></div></h3>
                    <h3>Payment Status: <div class="info-label"><?php echo e(ucfirst($registration->payment_status)); ?></div></h3>
                    <?php if($registration->payment_completed_at): ?>
                        <h3>Payment Date: <div class="info-label"><?php echo e($registration->payment_completed_at->format('d/m/Y')); ?></div></h3>
                    <?php endif; ?>
                </div>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th class="number-col">#</th>
                        <th class="item-col">Item</th>
                        <th class="description-col">Description</th>
                        <th class="qty-col">Qty</th>
                        <th class="rate-col">Rate($)</th>
                        <th class="amount-col">Amount($)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="number-col">1</td>
                        <td class="item-col"><?php echo e($registration->package->name); ?></td>
                        <td class="description-col">
                            <?php echo e($registration->package->description ?? 'Conference Registration Package'); ?>

                            <?php if($registration->registration_type === 'group'): ?>
                                <br><small>Group Registration (<?php echo e($registration->participants->count()); ?> participants)</small>
                            <?php endif; ?>
                        </td>
                        <td class="qty-col">1</td>
                        <td class="rate-col"><?php echo e(number_format($registration->total_amount, 2)); ?></td>
                        <td class="amount-col"><?php echo e(number_format($registration->total_amount, 2)); ?></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="4"><strong>Total (USD)</strong></td>
                        <td colspan="2" class="amount-col"><strong><?php echo e(number_format($registration->total_amount, 2)); ?></strong></td>
                    </tr>
                </tbody>
            </table>

            <?php if($registration->registration_type === 'group' && $registration->participants->count() > 0): ?>
                <div style="margin-top: 20px;">
                    <h3 style="font-size: 12pt; font-weight: bold; color: #374151; margin: 0 0 10px 0;">Group Participants</h3>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 20%;">Name</th>
                                <th style="width: 25%;">Email</th>
                                <th style="width: 20%;">Organization</th>
                                <th style="width: 15%;">Nationality</th>
                                <th style="width: 15%;">Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $registration->participants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $participant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td style="text-align: center;"><?php echo e($index + 1); ?></td>
                                    <td><?php echo e($participant->first_name); ?> <?php echo e($participant->last_name); ?></td>
                                    <td><?php echo e($participant->email); ?></td>
                                    <td><?php echo e($participant->organization ?? 'N/A'); ?></td>
                                    <td><?php echo e($participant->nationality ?? 'N/A'); ?></td>
                                    <td><?php echo e($participant->phone ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="bank-details">
                <h3>Direct Deposit Bank Account Details:</h3>
                <p><strong>Account Name:</strong> AFRICA CDC-CPHIA FUND ACCOUNT</p>
                <p><strong>Bank:</strong> ECO BANK</p>
                <p><strong>Acc. Number USD:</strong> 6640006767</p>
                <p><strong>Swift:</strong> ECOCKENA</p>
                <p><strong>Branch:</strong> Westlands, Kenya</p>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/invoices/registration.blade.php ENDPATH**/ ?>