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
        .header h1 {
            font-size: 32px;
            font-weight: bold;
            color: #1a5632;
            margin: 0 0 10px 0;
        }
        .header p {
            font-size: 16px;
            color: #666;
            margin: 0;
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
            text-align: left;
        }
        .bill-to h3, .receipt-info h3 {
            font-size: 14px;
            font-weight: bold;
            color: #1F2937;
            margin: 0 0 10px 0;
        }
        .bill-to p, .receipt-info p {
            margin: 3px 0;
            font-size: 11pt;
        }
        .receipt-info p strong {
            font-weight: bold;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20mm 0;
            background-color: #FFFFFF;
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
            vertical-align: top;
        }
        .items-table .number-col {
            width: 5%;
            text-align: center;
        }
        .items-table .item-col {
            width: 20%;
        }
        .items-table .description-col {
            width: 40%;
        }
        .items-table .qty-col {
            width: 10%;
            text-align: center;
        }
        .items-table .rate-col {
            width: 12%;
            text-align: right;
        }
        .items-table .amount-col {
            width: 13%;
            text-align: right;
        }
        .total-row {
            background-color: #F3F4F6;
            font-weight: bold;
        }
        .total-row td {
            border-top: 2px solid #1F2937;
        }
        .bank-details {
            margin-top: 20mm;
            padding: 15px;
            background-color: #F3F4F6;
            border: 1px solid #D1D5DB;
        }
        .bank-details h3 {
            font-size: 12pt;
            font-weight: bold;
            color: #1F2937;
            margin: 0 0 10px 0;
        }
        .bank-details p {
            margin: 3px 0;
            font-size: 10pt;
        }
        .footer {
            margin-top: 20mm;
            text-align: center;
            font-size: 9pt;
            color: #6B7280;
        }
        .footer p {
            margin: 2px 0;
        }
        .qr-codes {
            margin: 15mm 0;
            text-align: center;
        }
        .qr-code {
            display: inline-block;
            margin: 0 10px;
            text-align: center;
        }
        .qr-code img {
            width: 80px;
            height: 80px;
            border: 1px solid #D1D5DB;
        }
        .qr-info {
            font-size: 8pt;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content-wrapper">
            <div class="header">
                <h1>Receipt</h1>
                <p>CPHIA 2025 - 4th International Conference on Public Health in Africa</p>
            </div>

            <div class="receipt-details">
                <div class="bill-to">
                    <h3>Bill To</h3>
                    <p><strong>{{ $user->first_name }} {{ $user->last_name }}</strong></p>
                    <p>{{ $user->email }}</p>
                    @if($user->phone)
                        <p>Phone: {{ $user->phone }}</p>
                    @endif
                    @if($user->organization)
                        <p>Organization: {{ $user->organization }}</p>
                    @endif
                    @if($user->institution)
                        <p>Institution: {{ $user->institution }}</p>
                    @endif
                </div>
                
                <div class="receipt-info">
                    <h3>Receipt Details</h3>
                    <p><strong>Receipt Number:</strong> {{ $registration->id }}</p>
                    <p><strong>Registration Type:</strong> {{ ucfirst($registration->registration_type) }}</p>
                    <p><strong>Payment Status:</strong> {{ ucfirst($registration->payment_status) }}</p>
                    <p><strong>Date:</strong> {{ $registration->created_at->format('M d, Y') }}</p>
                    <p><strong>Package:</strong> {{ $package->name }}</p>
                    @if($registration->payment_method)
                        <p><strong>Payment Method:</strong> {{ ucfirst($registration->payment_method) }}</p>
                    @endif
                </div>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th class="number-col">#</th>
                        <th class="item-col">Item</th>
                        <th class="description-col">Description</th>
                        <th class="qty-col">Qty</th>
                        <th class="rate-col">Rate (USD)</th>
                        <th class="amount-col">Amount (USD)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="number-col">1</td>
                        <td class="item-col">{{ $package->name }}</td>
                        <td class="description-col">{{ $package->description }}</td>
                        <td class="qty-col">1</td>
                        <td class="rate-col">{{ number_format($registration->total_amount, 2) }}</td>
                        <td class="amount-col">{{ number_format($registration->total_amount, 2) }}</td>
                    </tr>
                    @if($registration->registration_type === 'group' && $participants->count() > 0)
                        @foreach($participants as $index => $participant)
                        <tr>
                            <td class="number-col">{{ $index + 2 }}</td>
                            <td class="item-col">Group Participant</td>
                            <td class="description-col">{{ $participant->first_name }} {{ $participant->last_name }} ({{ $participant->email }})</td>
                            <td class="qty-col">1</td>
                            <td class="rate-col">$0.00</td>
                            <td class="amount-col">$0.00</td>
                        </tr>
                        @endforeach
                    @endif
                    <tr class="total-row">
                        <td colspan="4"><strong>Total (USD)</strong></td>
                        <td colspan="2" class="amount-col"><strong>{{ number_format($registration->total_amount, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>

            @if($registration->registration_type === 'group' && $participants->count() > 0)
            <div class="participants-section">
                <h3>Group Participants ({{ $participants->count() }} total)</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Organization</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($participants as $participant)
                        <tr>
                            <td>{{ $participant->first_name }} {{ $participant->last_name }}</td>
                            <td>{{ $participant->email }}</td>
                            <td>{{ $participant->organization ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <div class="qr-codes">
                <div class="qr-code">
                    <img src="data:image/svg+xml;base64,{{ base64_encode('<svg width="80" height="80" xmlns="http://www.w3.org/2000/svg"><rect width="80" height="80" fill="white"/><text x="40" y="40" text-anchor="middle" font-size="8" fill="black">QR Code</text></svg>') }}" alt="Receipt QR Code">
                    <div class="qr-info">Receipt Verification</div>
                </div>
                <div class="qr-code">
                    <img src="data:image/svg+xml;base64,{{ base64_encode('<svg width="80" height="80" xmlns="http://www.w3.org/2000/svg"><rect width="80" height="80" fill="white"/><text x="40" y="40" text-anchor="middle" font-size="8" fill="black">QR Code</text></svg>') }}" alt="Attendance QR Code">
                    <div class="qr-info">Attendance Verification</div>
                </div>
            </div>

            <div class="bank-details">
                <h3>Payment Information</h3>
                <p><strong>Account Name:</strong> AFRICA CDC-CPHIA FUND ACCOUNT</p>
                <p><strong>Bank:</strong> ECO BANK</p>
                <p><strong>Account Number (USD):</strong> 6640006767</p>
                <p><strong>Swift Code:</strong> ECOCKENA</p>
                <p><strong>Branch:</strong> Westlands, Kenya</p>
                <p><strong>Reference:</strong> {{ $registration->id }}</p>
            </div>

            <div class="footer">
                <p><strong>CPHIA 2025</strong> - 4th International Conference on Public Health in Africa</p>
                <p>22-25 October 2025 • Durban, South Africa</p>
                <p>For questions, contact: support@cphia2025.com</p>
            </div>
        </div>
    </div>
</body>
</html>
