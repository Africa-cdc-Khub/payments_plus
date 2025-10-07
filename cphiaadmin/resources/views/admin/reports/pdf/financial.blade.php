<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report - CPHIA 2025</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
        .header h1 { color: #28a745; margin: 0; font-size: 20px; }
        .stats { background-color: #f8f9fa; padding: 15px; margin-bottom: 20px; }
        .stats p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 6px; text-align: left; border: 1px solid #ddd; font-size: 10px; }
        th { background-color: #28a745; color: white; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CPHIA 2025 - Financial Report</h1>
        <p style="color: #666;">Generated: {{ $generated_at }}</p>
    </div>

    <div class="stats">
        <strong>Financial Summary:</strong>
        <p>Total Transactions: {{ $stats['total_transactions'] }} | Completed: {{ $stats['completed'] }} | Pending: {{ $stats['pending'] }} | Failed: {{ $stats['failed'] }}</p>
        <p>Total Amount: ${{ number_format($stats['total_amount'], 2) }}</p>
        <p>Completed: ${{ number_format($stats['completed_amount'], 2) }} | Pending: ${{ number_format($stats['pending_amount'], 2) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Transaction UUID</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Method</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>#{{ $payment->id }}</td>
                <td>{{ substr($payment->transaction_uuid ?? 'N/A', 0, 16) }}...</td>
                <td>{{ $payment->registration->user->first_name ?? 'N/A' }} {{ $payment->registration->user->last_name ?? '' }}</td>
                <td>${{ number_format($payment->amount, 2) }}</td>
                <td>{{ ucfirst($payment->payment_status) }}</td>
                <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                <td>{{ $payment->created_at->format('M d, Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>CPHIA 2025 Admin System | © {{ date('Y') }} All rights reserved</p>
    </div>
</body>
</html>

