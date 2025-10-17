<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #1F2937; }
        .container { max-width: 640px; margin: 0 auto; padding: 16px; }
        .header { margin-bottom: 16px; }
        .heading { font-size: 18px; font-weight: bold; }
        .meta { color: #6B7280; font-size: 12px; }
        .section { margin-top: 16px; }
        .label { font-weight: 600; color: #374151; }
        .value { color: #111827; }
        .footer { margin-top: 24px; color: #6B7280; font-size: 12px; }
    </style>
    </head>
<body>
    <div class="container">
        <div class="header">
            <div class="heading">Invoice {{ $invoice->invoice_number }}</div>
            <div class="meta">Date: {{ optional($invoice->created_at)->format('M d, Y') }}</div>
        </div>

        <div class="section">
            <div class="label">Bill To:</div>
            <div class="value">{{ $invoice->biller_name }} ({{ $invoice->biller_email }})</div>
            <div class="value" style="white-space: pre-wrap;">{{ $invoice->biller_address }}</div>
        </div>

        <div class="section">
            <div class="label">Item</div>
            <div class="value">{{ $invoice->item }}</div>
        </div>

        @if($invoice->description)
        <div class="section">
            <div class="label">Description</div>
            <div class="value">{{ $invoice->description }}</div>
        </div>
        @endif

        <div class="section">
            <div class="label">Amount</div>
            <div class="value">Qty: {{ $invoice->quantity }} × Rate: {{ number_format($invoice->rate, 2) }} {{ $invoice->currency }}</div>
            <div class="value" style="margin-top: 4px; font-weight: bold;">Total: {{ number_format($invoice->amount, 2) }} {{ $invoice->currency }}</div>
        </div>

        <div class="footer">
            This invoice is attached as a PDF.
        </div>
    </div>
</body>
</html>


