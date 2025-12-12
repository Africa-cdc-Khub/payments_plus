<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - CPHIA 2025</title>
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

        .receipt-details {
            display: table;
            width: 100%;
            margin-bottom: 20mm;
        }
        .bill-to {
            display: table-cell;
            width: 60%;
            vertical-align: top;
            padding-right: 20px;
        }
        .receipt-info {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: right;
        }

        .bill-to h3, .receipt-info h3 {
            font-size: 12pt;
            font-weight: bold;
            color: #1F2937;
            margin: 0 0 8px 0;
        }

        .bill-to p, .receipt-info p {
            margin: 4px 0;
            font-size: 11pt;
            line-height: 1.3;
        }

        .info-label {
            font-weight: bold;
            color: #1F2937;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20mm;
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
            margin-top: 20mm;
            text-align: center;
            padding: 0;
        }

        .footer img {
            width: 100%;
            height: auto;
            display: block;
        }

        .payment-info {
            margin-top: 15mm;
            padding: 12px;
            background-color: #F3F4F6;
            border: 1px solid #D1D5DB;
        }

        .payment-info h3 {
            font-size: 11pt;
            font-weight: bold;
            color: #1F2937;
            margin: 0 0 8px 0;
        }

        .payment-info p {
            margin: 4px 0;
            font-size: 10pt;
        }

        @media print {
            body {
                font-size: 10pt;
            }
            
            .header {
                margin-bottom: 12mm;
            }
            
            .receipt-details {
                margin-bottom: 15mm;
            }
            
            .items-table {
                margin-bottom: 15mm;
            }
            
            .payment-info {
                margin-top: 12mm;
            }
            
            .footer {
                margin-top: 15mm;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content-wrapper">
            <header class="header">
                <img src="<?php echo e(public_path('images/invoice-top.png')); ?>" alt="CPHIA 2025 Receipt Header" />
            </header>

            <div class="receipt-details">
                <div class="bill-to">
                    <h3>Bill To:</h3>
                    <p><strong><?php echo e($invoice->biller_name); ?></strong></p>
                    <p><?php echo e($invoice->biller_email); ?></p>
                    <?php if($invoice->biller_address): ?>
                    <p><?php echo e($invoice->biller_address); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="receipt-info">
                    <h3>Receipt <div class="info-label">RCPT-<?php echo e(str_pad($invoice->id, 6, '0', STR_PAD_LEFT)); ?></div></h3>
                    <h3>Date: <div class="info-label"><?php echo e($invoice->paid_at ? $invoice->paid_at->format('d/m/Y') : now()->format('d/m/Y')); ?></div></h3>
                    <h3>Payment Status: <div class="info-label">Completed</div></h3>
                    <?php if($invoice->paid_at): ?>
                    <h3>Payment Date: <div class="info-label"><?php echo e($invoice->paid_at->format('d/m/Y')); ?></div></h3>
                    <?php endif; ?>
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
        </div>
    </div>
</body>
</html><?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/invoices/receipt.blade.php ENDPATH**/ ?>