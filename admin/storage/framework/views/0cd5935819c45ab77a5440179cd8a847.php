<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?php echo e($invoice->invoice_number); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #333;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px 0;
            background: white;
        }

        .header img {
            max-width: 100%;
            height: auto;
        }

        .receipt-title {
            font-size: 24pt;
            font-weight: bold;
            color: #1a5632;
            margin: 20px 0 10px 0;
        }

        .receipt-number {
            font-size: 14pt;
            color: #666;
            margin-bottom: 20px;
        }

        .info-label {
            font-weight: bold;
            color: #1a5632;
        }

        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .bill-to, .receipt-info-section {
            flex: 1;
        }

        .bill-to h3, .receipt-info-section h3 {
            font-size: 12pt;
            font-weight: bold;
            color: #1a5632;
            margin: 0 0 10px 0;
        }

        .bill-to p, .receipt-info-section p {
            margin: 5px 0;
            font-size: 11pt;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            background-color: #F3F4F6;
            border: 1px solid #D1D5DB;
            padding: 8px 12px;
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
            color: #1F2937;
        }

        .items-table td {
            border: 1px solid #D1D5DB;
            padding: 8px 12px;
            font-size: 10pt;
        }

        .total-row {
            background-color: #F3F4F6;
            font-weight: bold;
        }

        .total-row td {
            border-top: 2px solid #1F2937;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            padding: 20px 0;
        }

        .footer img {
            max-width: 100%;
            height: auto;
        }

        .payment-info {
            margin-top: 30px;
            padding: 15px;
            background-color: #F3F4F6;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
        }

        .payment-info h3 {
            font-size: 12pt;
            font-weight: bold;
            color: #1F2937;
            margin: 0 0 10px 0;
        }

        .payment-info p {
            margin: 5px 0;
            font-size: 11pt;
        }

        @media print {
            body {
                font-size: 11pt;
            }
            
            .header {
                margin-bottom: 20px;
            }
            
            .receipt-title {
                font-size: 20pt;
            }
            
            .items-table th,
            .items-table td {
                font-size: 9pt;
                padding: 6px 8px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <img src="<?php echo e(public_path('images/invoice-top.png')); ?>" alt="CPHIA 2025 Receipt Header" />
    </header>

    <div class="receipt-title">Receipt</div>
    
    <div class="receipt-number">
        Receipt <span class="info-label">INV-<?php echo e(str_pad($invoice->id, 6, '0', STR_PAD_LEFT)); ?></span>
    </div>

    <div class="receipt-info">
        <div class="bill-to">
            <h3>Bill To:</h3>
            <p><strong><?php echo e($invoice->biller_name); ?></strong></p>
            <p><?php echo e($invoice->biller_email); ?></p>
            <?php if($invoice->biller_address): ?>
            <p><?php echo e($invoice->biller_address); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="receipt-info-section">
            <h3>Receipt Information:</h3>
            <p><strong>Receipt Date:</strong> <?php echo e($invoice->paid_at ? $invoice->paid_at->format('M d, Y') : now()->format('M d, Y')); ?></p>
            <p><strong>Payment Date:</strong> <?php echo e($invoice->paid_at ? $invoice->paid_at->format('M d, Y') : 'N/A'); ?></p>
            <p><strong>Status:</strong> <span style="color: #1a5632; font-weight: bold;">Paid</span></p>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>Rate</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo e($invoice->item); ?></td>
                <td><?php echo e($invoice->description ?: 'N/A'); ?></td>
                <td><?php echo e($invoice->quantity); ?></td>
                <td><?php echo e($invoice->currency); ?> <?php echo e(number_format($invoice->rate, 2)); ?></td>
                <td><?php echo e($invoice->currency); ?> <?php echo e(number_format($invoice->amount, 2)); ?></td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4"><strong>Total Amount:</strong></td>
                <td><strong><?php echo e($invoice->currency); ?> <?php echo e(number_format($invoice->amount, 2)); ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="payment-info">
        <h3>Payment Information</h3>
        <p><strong>Payment Status:</strong> <span style="color: #1a5632; font-weight: bold;">Completed</span></p>
        <p><strong>Payment Date:</strong> <?php echo e($invoice->paid_at ? $invoice->paid_at->format('M d, Y H:i') : 'N/A'); ?></p>
        <?php if($invoice->paidBy): ?>
        <p><strong>Processed By:</strong> <?php echo e($invoice->paidBy->name ?? 'Admin'); ?></p>
        <?php endif; ?>
    </div>

    <div class="footer">
        <img src="<?php echo e(public_path('images/bottom-banner.png')); ?>" alt="CPHIA 2025 Footer" />
    </div>
</body>
</html>
<?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/invoices/receipt.blade.php ENDPATH**/ ?>