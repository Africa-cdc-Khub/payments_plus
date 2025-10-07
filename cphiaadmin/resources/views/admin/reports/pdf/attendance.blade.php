<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report - CPHIA 2025</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #dc3545; padding-bottom: 10px; }
        .header h1 { color: #dc3545; margin: 0; font-size: 20px; }
        .stats { background-color: #f8f9fa; padding: 15px; margin-bottom: 20px; }
        .stats p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 6px; text-align: left; border: 1px solid #ddd; font-size: 10px; }
        th { background-color: #dc3545; color: white; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CPHIA 2025 - Attendance Report</h1>
        <p style="color: #666;">Generated: {{ $generated_at }}</p>
    </div>

    <div class="stats">
        <strong>Attendance Summary:</strong>
        <p>Total Participants: {{ $stats['total_participants'] }} | Present: {{ $stats['present'] }} | Absent: {{ $stats['absent'] }} | Pending: {{ $stats['pending'] }}</p>
        <p>Attendance Rate: {{ $stats['attendance_rate'] }}%</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Organization</th>
                <th>Category</th>
                <th>Nationality</th>
                <th>Attendance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->organization ?? 'N/A' }}</td>
                <td>{{ $user->delegate_category ?? 'N/A' }}</td>
                <td>{{ $user->nationality ?? 'N/A' }}</td>
                <td>{{ ucfirst($user->attendance_status ?? 'pending') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>CPHIA 2025 Admin System | © {{ date('Y') }} All rights reserved</p>
    </div>
</body>
</html>

