<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Receipt - {{ $conference_short_name ?? 'CPHIA 2025' }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .receipt-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1a5632 0%, #2d7d32 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 -30px 15px -30px;
            width: calc(100% + 60px);
            gap: 20px;
            background: white;
            padding: 15px 30px;
            border-radius: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .logo {
            max-width: 150px;
            width: 150px;
            height: auto;
            display: block;
        }
        .africa-cdc-logo {
            max-width: 150px;
        }
        .cphia-logo {
            max-width: 150px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: white;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
            color: white;
        }
        .receipt-content {
            padding: 30px;
        }
        .receipt-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .receipt-title h2 {
            color: #1a5632;
            font-size: 24px;
            margin: 0 0 10px 0;
        }
        .receipt-title p {
            color: #666;
            font-size: 16px;
            margin: 0;
        }
        .receipt-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #495057;
            min-width: 120px;
        }
        .detail-value {
            color: #212529;
            text-align: right;
            flex: 1;
        }
        .qr-section {
            text-align: center;
            margin: 30px 0;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .qr-codes-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }
        .qr-code-main, .qr-code-verification, .qr-code-navigation {
            text-align: center;
        }
        .qr-code {
            max-width: 150px;
            width: 150px;
            height: 150px;
            margin: 15px 0;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .qr-code img {
            max-width: 100%;
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 4px;
        }
        .qr-info {
            color: #666;
            font-size: 14px;
            margin-top: 15px;
        }
        .amount-section {
            background: linear-gradient(135deg, #1a5632 0%, #2d7d32 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            margin: 25px 0;
        }
        .amount-label {
            font-size: 16px;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        .amount-value {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 25px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .footer p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .contact-info {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        .contact-info h4 {
            color: #1a5632;
            margin-bottom: 15px;
        }
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        .contact-item i {
            width: 20px;
            color: #1a5632;
            margin-right: 10px;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .receipt-content {
                padding: 20px;
            }
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
            }
            .detail-value {
                text-align: left;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <div class="logo-container">
                <img src="https://africacdc.org/wp-content/uploads/2020/02/AfricaCDC_Logo.png" alt="Africa CDC" class="logo africa-cdc-logo">
                <img src="https://cphia2025.com/wp-content/uploads/2025/09/CPHIA-2025-logo_reverse.png" alt="CPHIA 2025" class="logo cphia-logo">
            </div>
            <h1>{{ $conference_short_name ?? 'CPHIA 2025' }}</h1>
            <p>{{ $conference_dates ?? '22-25 October 2025' }} • {{ $conference_location ?? 'Durban, South Africa' }}</p>
        </div>
        
        <div class="receipt-content">
            <div class="receipt-title">
                <h2>{{ $registration_type === 'group' ? 'Group Registration Receipt' : 'Registration Receipt' }}</h2>
                <p>Payment Confirmation</p>
            </div>
            
            <div class="receipt-details">
                <div class="detail-row">
                    <span class="detail-label">Registration ID:</span>
                    <span class="detail-value">#{{ $registration_id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Participant Name:</span>
                    <span class="detail-value">{{ $participant_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">{{ $participant_email }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">{{ $phone ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Package:</span>
                    <span class="detail-value">{{ $package_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Organization:</span>
                    <span class="detail-value">{{ $organization ?? 'N/A' }}</span>
                </div>
                @if($institution)
                <div class="detail-row">
                    <span class="detail-label">Institution:</span>
                    <span class="detail-value">{{ $institution }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Nationality:</span>
                    <span class="detail-value">{{ $nationality ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Date:</span>
                    <span class="detail-value">{{ $payment_date }}</span>
                </div>
            </div>
            
            <div class="qr-section">
                <h4 style="color: #1a5632; margin-bottom: 15px;">Registration QR Codes</h4>
                <div class="qr-codes-container">
                    <div class="qr-code-main">
                        <div class="qr-code">
                            <img src="{{ $main_qr_code }}" alt="Full Registration QR Code">
                        </div>
                        <p class="qr-info">
                            <strong>Complete Receipt</strong><br>
                            All registration details
                        </p>
                    </div>
                    <div class="qr-code-verification">
                        <div class="qr-code">
                            <img src="{{ $verification_qr_code }}" alt="Verification QR Code">
                        </div>
                        <p class="qr-info">
                            <strong>Quick Check-in</strong><br>
                            Fast attendance scanning
                        </p>
                    </div>
                    <div class="qr-code-navigation">
                        <div class="qr-code">
                            <img src="{{ $navigation_qr_code }}" alt="Verification Link QR Code">
                        </div>
                        <p class="qr-info">
                            <strong>Verify Online</strong><br>
                            Scan to verify attendance
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="amount-section">
                <div class="amount-label">Total Amount Paid</div>
                <div class="amount-value">{{ $total_amount }}</div>
            </div>
            
            <div class="contact-info">
                <h4>Contact Information</h4>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>{{ $support_email ?? 'support@cphia2025.com' }}</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-globe"></i>
                    <span>https://cphia2025.com</span>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>Thank you for registering for {{ $conference_name ?? 'CPHIA 2025' }}!</strong></p>
            <p>This receipt serves as confirmation of your registration and payment.</p>
            <p>Please keep this receipt for your records and bring it to the conference.</p>
        </div>
    </div>
</body>
</html>