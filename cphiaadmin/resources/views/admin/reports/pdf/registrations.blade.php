<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Registrations Report - CPHIA 2025</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .header h1 { color: #007bff; margin: 0; font-size: 20px; }
        .stats { background-color: #f8f9fa; padding: 15px; margin-bottom: 20px; }
        .stats p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 6px; text-align: left; border: 1px solid #ddd; font-size: 10px; }
        th { background-color: #007bff; color: white; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 9px; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CPHIA 2025 - Registrations Report</h1>
        <p style="color: #666;">Generated: {{ $generated_at }}</p>
    </div>

    <div class="stats">
        <strong>Summary Statistics:</strong>
        <p>Total: {{ $stats['total'] }} | Individual: {{ $stats['individual'] }} | Group: {{ $stats['group'] }}</p>
        <p>Completed: {{ $stats['completed'] }} | Pending: {{ $stats['pending'] }}</p>
        <p>Total Amount: ${{ number_format($stats['total_amount'], 2) }} | Paid: ${{ number_format($stats['paid_amount'], 2) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Registrant</th>
                <th>Type</th>
                <th>Package</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registrations as $reg)
            <tr>
                <td>#{{ $reg->id }}</td>
                <td>{{ $reg->user->first_name ?? 'N/A' }} {{ $reg->user->last_name ?? '' }}</td>
                <td>{{ ucfirst($reg->registration_type) }}</td>
                <td>{{ $reg->package->name ?? 'N/A' }}</td>
                <td>${{ number_format($reg->total_amount, 2) }}</td>
                <td>{{ ucfirst($reg->status) }}</td>
                <td>{{ $reg->created_at->format('M d, Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>CPHIA 2025 Admin System | © {{ date('Y') }} All rights reserved</p>
    </div>
</body>
</html>

