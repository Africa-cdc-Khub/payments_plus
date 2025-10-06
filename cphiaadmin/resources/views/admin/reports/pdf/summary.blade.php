<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Summary Report - CPHIA 2025</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #007bff;
            margin: 0 0 5px 0;
        }
        .header p {
            color: #666;
            margin: 5px 0;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stats-row {
            display: table-row;
        }
        .stats-cell {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            vertical-align: top;
        }
        .stat-box {
            border: 2px solid #ddd;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
        }
        .stat-box h2 {
            font-size: 32px;
            margin: 0;
            color: #007bff;
        }
        .stat-box p {
            margin: 5px 0 0 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .section-title {
            background-color: #007bff;
            color: white;
            padding: 10px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CPHIA 2025 - Summary Report</h1>
        <p>Conference Report Summary</p>
        <p>Period: {{ \Carbon\Carbon::parse($date_from)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($date_to)->format('M d, Y') }}</p>
        <p>Generated: {{ $generated_at }}</p>
    </div>

    <div class="stats-grid">
        <div class="stats-row">
            <div class="stats-cell">
                <div class="stat-box">
                    <h2>{{ $registrations['total'] }}</h2>
                    <p>Total Registrations</p>
                </div>
            </div>
            <div class="stats-cell">
                <div class="stat-box">
                    <h2>${{ number_format($payments['completed'], 2) }}</h2>
                    <p>Revenue (Completed)</p>
                </div>
            </div>
            <div class="stats-cell">
                <div class="stat-box">
                    <h2>{{ $participants['total'] }}</h2>
                    <p>Total Participants</p>
                </div>
            </div>
        </div>
    </div>

    <div class="section-title">Registrations Overview</div>
    <table>
        <tr>
            <th>Metric</th>
            <th>Count</th>
        </tr>
        <tr>
            <td>Total Registrations</td>
            <td><strong>{{ $registrations['total'] }}</strong></td>
        </tr>
        <tr>
            <td>Individual Registrations</td>
            <td>{{ $registrations['individual'] }}</td>
        </tr>
        <tr>
            <td>Group Registrations</td>
            <td>{{ $registrations['group'] }}</td>
        </tr>
        <tr>
            <td>Completed</td>
            <td>{{ $registrations['completed'] }}</td>
        </tr>
    </table>

    <div class="section-title">Financial Overview</div>
    <table>
        <tr>
            <th>Metric</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Total Amount</td>
            <td><strong>${{ number_format($payments['total'], 2) }}</strong></td>
        </tr>
        <tr>
            <td>Completed Payments</td>
            <td>${{ number_format($payments['completed'], 2) }}</td>
        </tr>
        <tr>
            <td>Pending Payments</td>
            <td>${{ number_format($payments['pending'], 2) }}</td>
        </tr>
        <tr>
            <td>Total Transactions</td>
            <td>{{ $payments['count'] }}</td>
        </tr>
    </table>

    <div class="section-title">Participants Overview</div>
    <table>
        <tr>
            <th>Metric</th>
            <th>Count</th>
        </tr>
        <tr>
            <td>Total Participants</td>
            <td><strong>{{ $participants['total'] }}</strong></td>
        </tr>
        <tr>
            <td>Require Visa</td>
            <td>{{ $participants['requires_visa'] }}</td>
        </tr>
        <tr>
            <td>Present</td>
            <td>{{ $participants['present'] }}</td>
        </tr>
        <tr>
            <td>Absent</td>
            <td>{{ $participants['absent'] }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>This report was automatically generated by the CPHIA 2025 Admin System</p>
        <p>© {{ date('Y') }} CPHIA 2025. All rights reserved.</p>
    </div>
</body>
</html>

