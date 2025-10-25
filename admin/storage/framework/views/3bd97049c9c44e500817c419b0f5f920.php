<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?php echo e($invoice->invoice_number); ?></title>
    <style>
        body {
            font-family: 'Open Sans', 'Helvetica', 'Arial', sans-serif;
            background-color: #FFFFFF;
            color: #1F2937;
            margin: 0;
            padding: 20px;
            font-size: 14px;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: #1a5632;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .invoice-details {
            display: table;
            width: 100%;
            margin-bottom: 30px;
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
            font-size: 16px;
            font-weight: bold;
            color: #1F2937;
            margin: 0 0 15px 0;
        }
        .bill-to p, .invoice-info p {
            margin: 5px 0;
            font-size: 14px;
        }
        .invoice-info p strong {
            font-weight: bold;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            background-color: #FFFFFF;
        }
        .items-table th {
            background-color: #F3F4F6;
            border: 1px solid #D1D5DB;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 14px;
            color: #1F2937;
        }
        .items-table td {
            border: 1px solid #D1D5DB;
            padding: 12px;
            font-size: 14px;
            vertical-align: top;
        }
        .items-table .number-col {
            width: 5%;
            text-align: center;
        }
        .items-table .item-col {
            width: 20%;
        }
        .items-table .description-col {
            width: 40%;
        }
        .items-table .qty-col {
            width: 10%;
            text-align: center;
        }
        .items-table .rate-col {
            width: 12%;
            text-align: right;
        }
        .items-table .amount-col {
            width: 13%;
            text-align: right;
        }
        .total-row {
            background-color: #F3F4F6;
            font-weight: bold;
        }
        .total-row td {
            border-top: 2px solid #1F2937;
        }
        .bank-details {
            margin-top: 30px;
            padding: 20px;
            background-color: #F3F4F6;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
        }
        .bank-details h3 {
            font-size: 16px;
            font-weight: bold;
            color: #1F2937;
            margin: 0 0 15px 0;
        }
        .bank-details p {
            margin: 5px 0;
            font-size: 14px;
        }
        .footer {
            background-color: #F9FAFB;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #E5E7EB;
        }
        .footer p {
            margin: 5px 0;
            font-size: 12px;
            color: #6B7280;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #FEF3C7;
            color: #92400E;
        }
        .status-paid {
            background-color: #D1FAE5;
            color: #065F46;
        }
        .status-cancelled {
            background-color: #FEE2E2;
            color: #991B1B;
        }
        @media (max-width: 600px) {
            .invoice-details {
                display: block;
            }
            .bill-to, .invoice-info {
                display: block;
                width: 100%;
                padding-right: 0;
                margin-bottom: 20px;
            }
            .items-table {
                font-size: 12px;
            }
            .items-table th, .items-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Receipt</h1>
            <p>CPHIA 2025 - 4th International Conference on Public Health in Africa</p>
        </div>

        <div class="content">
            <div class="invoice-details">
                <div class="bill-to">
                    <h3>Receipt To</h3>
                    <p><strong><?php echo e($invoice->biller_name); ?></strong></p>
                    <p><?php echo e($invoice->biller_email); ?></p>
                    <p style="white-space: pre-line;"><?php echo e($invoice->biller_address); ?></p>
                </div>
                
                <div class="invoice-info">
                    <h3>Receipt Details</h3>
                    <p><strong>Invoice Number:</strong> <?php echo e($invoice->invoice_number); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge status-<?php echo e($invoice->status); ?>">
                            <?php echo e(ucfirst($invoice->status)); ?>

                        </span>
                    </p>
                    <p><strong>Date:</strong> <?php echo e($invoice->created_at->format('M d, Y')); ?></p>
                    <p><strong>Due Date:</strong> <?php echo e($invoice->created_at->addDays(30)->format('M d, Y')); ?></p>
                    <?php if($invoice->paid_at): ?>
                        <p><strong>Paid Date:</strong> <?php echo e($invoice->paid_at->format('M d, Y')); ?></p>
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
                        <th class="rate-col">Rate (<?php echo e($invoice->currency); ?>)</th>
                        <th class="amount-col">Amount (<?php echo e($invoice->currency); ?>)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="number-col">1</td>
                        <td class="item-col"><?php echo e($invoice->item); ?></td>
                        <td class="description-col"><?php echo e($invoice->description); ?></td>
                        <td class="qty-col"><?php echo e($invoice->quantity); ?></td>
                        <td class="rate-col"><?php echo e(number_format($invoice->rate, 2)); ?></td>
                        <td class="amount-col"><?php echo e(number_format($invoice->amount, 2)); ?></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="4"><strong>Total (<?php echo e($invoice->currency); ?>)</strong></td>
                        <td colspan="2" class="amount-col"><strong><?php echo e(number_format($invoice->amount, 2)); ?></strong></td>
                    </tr>
                </tbody>
            </table>

            <div class="bank-details" style="display: none !important;">
                <h3>Payment Information</h3>
                <p><strong>Account Name:</strong> AFRICA CDC-CPHIA FUND ACCOUNT</p>
                <p><strong>Bank:</strong> ECO BANK</p>
                <p><strong>Account Number (USD):</strong> 6640006767</p>
                <p><strong>Swift Code:</strong> ECOCKENA</p>
                <p><strong>Branch:</strong> Westlands, Kenya</p>
                <p><strong>Reference:</strong> <?php echo e($invoice->invoice_number); ?></p>
            </div>
        </div>

        <div class="footer">
            <p><strong>CPHIA 2025</strong> - 4th International Conference on Public Health in Africa</p>
            <p>22-25 October 2025 • Durban, South Africa</p>
            <p>For questions, contact: support@cphia2025.com</p>
        </div>
    </div>
</body>
</html>
<?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/invoices/email.blade.php ENDPATH**/ ?>