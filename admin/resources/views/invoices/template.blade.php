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
            /* background-image: url('{{ public_path('images/invoice-top.png') }}');
            background-size: 80% auto;
            background-position: center;
            background-repeat: no-repeat;
            height: 35mm;
            top: 10px; */
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
            <img src="{{ public_path('images/invoice-top.png') }}" alt="CPHIA 2025 Invoice Header" />
        </header>

        <div class="content-wrapper">
            <div class="invoice-details">
                <div class="bill-to">
                    <h3>Bill To</h3>
                    <p><strong>{{ $invoice->biller_name }}</strong></p>
                    <p>{{ $invoice->biller_address }}</p>
                </div>
                
                <div class="invoice-info">
                    <h3>Invoice <div class="info-label">{{ $invoice->invoice_number }}</div></h3>
                    <h3>Terms: <div class="info-label">Upon Invoice Reception</div></h3>
                    <h3>Due Date: <div class="info-label">{{ $invoice->created_at->addDays(30)->format('d/m/Y') }}</div></h3>
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
                        <td class="item-col">{{ $invoice->item }}</td>
                        <td class="description-col">{{ $invoice->description }}</td>
                        <td class="qty-col">{{ $invoice->quantity }}</td>
                        <td class="rate-col">{{ number_format($invoice->rate, 2) }}</td>
                        <td class="amount-col">{{ number_format($invoice->amount, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="4"><strong>Total ({{ $invoice->currency }})</strong></td>
                        <td colspan="2" class="amount-col"><strong>{{ number_format($invoice->amount, 0) }}</strong></td>
                    </tr>
                </tbody>
            </table>

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
