<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $registration->id }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #1F2937; }
        .container { max-width: 640px; margin: 0 auto; padding: 16px; }
        .header { margin-bottom: 16px; }
        .heading { font-size: 18px; font-weight: bold; color: #1a5632; }
        .meta { color: #6B7280; font-size: 12px; }
        .section { margin-top: 16px; }
        .label { font-weight: 600; color: #374151; }
        .value { color: #111827; }
        .footer { margin-top: 24px; color: #6B7280; font-size: 12px; }
        .highlight { background-color: #F3F4F6; padding: 12px; border-radius: 6px; margin: 12px 0; }
    </style>
    </head>
<body>
    <div class="container">
        <div class="header">
            <div class="heading">Receipt #RCP-{{ str_pad($registration->id, 6, '0', STR_PAD_LEFT) }}</div>
            <div class="meta">Date: {{ optional($registration->created_at)->format('M d, Y') }}</div>
        </div>

        <div class="section">
            <div class="label">Bill To:</div>
            <div class="value">{{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})</div>
            @if($user->phone)
                <div class="value">Phone: {{ $user->phone }}</div>
            @endif
            @if($user->organization)
                <div class="value">Organization: {{ $user->organization }}</div>
            @endif
        </div>

        <div class="section">
            <div class="label">Registration Details</div>
            <div class="value">Type: {{ ucfirst($registration->registration_type) }}</div>
            <div class="value">Package: {{ $package->name }}</div>
            <div class="value">Payment Status: {{ ucfirst($registration->payment_status) }}</div>
            @if($registration->payment_method)
                <div class="value">Payment Method: {{ ucfirst($registration->payment_method) }}</div>
            @endif
        </div>

        <div class="section">
            <div class="label">Amount</div>
            <div class="value">Total: ${{ number_format($registration->total_amount, 2) }} USD</div>
        </div>

        @if($registration->registration_type === 'group' && $registration->participants && $registration->participants->count() > 0)
        <div class="section">
            <div class="label">Group Participants ({{ $registration->participants->count() }} total)</div>
            @foreach($registration->participants as $participant)
                <div class="value">• {{ $participant->first_name }} {{ $participant->last_name }} ({{ $participant->email }})</div>
            @endforeach
        </div>
        @endif

        <div class="highlight">
            <strong>Important:</strong> Your receipt is attached as a PDF document. Please keep this receipt for your records.
        </div>

        <div class="footer">
            <p><strong>CPHIA 2025</strong> - 4th International Conference on Public Health in Africa</p>
            <p>22-25 October 2025 • Durban, South Africa</p>
            <p>For questions, contact: support@cphia2025.com</p>
        </div>
    </div>
</body>
</html>
