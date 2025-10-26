<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: #1a5632;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .logo-container {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin: 0 -30px 15px -30px;
            width: calc(100% + 60px);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
        .heading {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .content {
            padding: 30px 20px;
        }
        .receipt-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .receipt-number {
            font-size: 18px;
            font-weight: bold;
            color: #1a5632;
            margin-bottom: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background-color: #F3F4F6;
            border: 1px solid #D1D5DB;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            color: #1F2937;
        }
        .items-table td {
            border: 1px solid #D1D5DB;
            padding: 12px;
        }
        .total-row {
            background-color: #F3F4F6;
            font-weight: bold;
        }
        .total-row td {
            border-top: 2px solid #1F2937;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .footer p {
            margin: 5px 0;
        }
        .attachment-notice {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #1976d2;
        }
        .attachment-notice i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo-container">
                <img src="{{ asset('images/africa-cdc-logo.png') }}" alt="Africa CDC" class="logo">
                <img src="{{ asset('images/cphia-logo.png') }}" alt="CPHIA 2025" class="logo">
            </div>
            <div class="heading">Receipt #RCPT-{{ str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>

        <div class="content">
            <p>Dear {{ $invoice->biller_name }},</p>
            
            <p>Thank you for your payment. Please find your receipt attached to this email.</p>

            <div class="receipt-info">
                <div class="receipt-number">Receipt #RCPT-{{ str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</div>
                
                <div class="info-row">
                    <span class="info-label">Bill To:</span>
                    <span class="info-value">{{ $invoice->biller_name }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $invoice->biller_email }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Receipt Date:</span>
                    <span class="info-value">{{ $invoice->paid_at ? $invoice->paid_at->format('M d, Y') : now()->format('M d, Y') }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Payment Status:</span>
                    <span class="info-value" style="color: #1a5632; font-weight: bold;">Paid</span>
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
                        <td>{{ $invoice->item }}</td>
                        <td>{{ $invoice->description ?: 'N/A' }}</td>
                        <td>{{ $invoice->quantity }}</td>
                        <td>{{ $invoice->currency }} {{ number_format($invoice->rate, 2) }}</td>
                        <td>{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="4"><strong>Total Amount:</strong></td>
                        <td><strong>{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>

            <div class="attachment-notice">
                <i class="fas fa-paperclip"></i>
                <strong>Receipt PDF Attached:</strong> A detailed receipt has been attached to this email for your records.
            </div>

            <p>If you have any questions about this receipt, please don't hesitate to contact us.</p>
            
            <p>Thank you for your business!</p>
            
            <p>Best regards,<br>
            CPHIA 2025 Team<br>
            Africa CDC</p>
        </div>

        <div class="footer">
            <p><strong>4th International Conference on Public Health in Africa</strong></p>
            <p>Africa CDC | CPHIA 2025</p>
            <p>This is an automated receipt notification. Please keep this email for your records.</p>
        </div>
    </div>
</body>
</html>
