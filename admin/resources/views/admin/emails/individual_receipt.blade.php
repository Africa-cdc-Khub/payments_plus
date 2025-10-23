<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Receipt - CPHIA 2025</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1a5632 0%, #2d7d32 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .header h2 {
            margin: 10px 0 0 0;
            font-size: 20px;
            font-weight: 400;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .receipt-title {
            color: #1a5632;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }
        .receipt-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 18px;
            color: #1a5632;
        }
        .detail-label {
            font-weight: 500;
            color: #6c757d;
        }
        .detail-value {
            color: #333;
        }
        .qr-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .qr-codes {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        .qr-item {
            text-align: center;
        }
        .qr-item img {
            max-width: 150px;
            height: auto;
            border: 2px solid #e9ecef;
            border-radius: 8px;
        }
        .qr-label {
            font-size: 12px;
            color: #6c757d;
            margin-top: 8px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .footer p {
            margin: 5px 0;
            color: #6c757d;
            font-size: 14px;
        }
        .footer a {
            color: #3b82f6;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            width: 100%;
            gap: 20px;
        }
        .logo {
            max-width: 150px;
            height: auto;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .africa-cdc-logo {
            max-width: 120px;
        }
        .cphia-logo {
            max-width: 180px;
        }
        @media (max-width: 600px) {
            .qr-codes {
                flex-direction: column;
                align-items: center;
            }
            .detail-row {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo-container">
                <img src="https://africacdc.org/wp-content/uploads/2020/02/AfricaCDC_Logo.png" alt="Africa CDC" class="logo africa-cdc-logo">
                <img src="https://cphia2025.com/wp-content/uploads/2025/09/CPHIA-2025-logo_reverse.png" alt="CPHIA 2025" class="logo cphia-logo">
            </div>
            <h1>{{ $conference_short_name ?? 'CPHIA 2025' }}</h1>
            <h2>{{ $conference_name ?? '4th International Conference on Public Health in Africa' }}</h2>
            <p>{{ $conference_dates ?? '22-25 October 2025' }} | {{ $conference_location ?? 'Durban, South Africa' }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <h2 class="receipt-title">Registration Receipt</h2>
            
            <p>Dear {{ $participant_name ?? 'Participant' }},</p>
            
            <p>Thank you for your registration for CPHIA 2025. This is your official receipt confirming your payment and registration details.</p>

            <!-- Receipt Details -->
            <div class="receipt-details">
                <div class="detail-row">
                    <span class="detail-label">Registration ID:</span>
                    <span class="detail-value">#{{ $registration_id ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Participant Name:</span>
                    <span class="detail-value">{{ $participant_name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">{{ $participant_email ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">{{ $phone ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Organization:</span>
                    <span class="detail-value">{{ $organization ?? 'N/A' }}</span>
                </div>
                @if(isset($institution) && $institution)
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
                    <span class="detail-label">Package:</span>
                    <span class="detail-value">{{ $package_name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value">{{ $payment_method ?? 'Online Payment' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Date:</span>
                    <span class="detail-value">{{ $payment_date ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value">{{ $total_amount ?? '$0.00' }}</span>
                </div>
            </div>

            <!-- QR Codes Section -->
            <div class="qr-section">
                <h3 style="color: #1a5632; margin-bottom: 20px;">Registration QR Codes</h3>
                <p style="color: #6c757d; margin-bottom: 20px;">Please save these QR codes for easy access to your registration and conference information.</p>
                
                <div class="qr-codes">
                    @if(isset($qr_code) && $qr_code)
                    <div class="qr-item">
                        <img src="{{ $qr_code }}" alt="Registration QR Code">
                        <div class="qr-label">Registration Details</div>
                    </div>
                    @endif
                    
                    @if(isset($verification_qr_code) && $verification_qr_code)
                    <div class="qr-item">
                        <img src="{{ $verification_qr_code }}" alt="Verification QR Code">
                        <div class="qr-label">Quick Verification</div>
                    </div>
                    @endif
                    
                    @if(isset($navigation_qr_code) && $navigation_qr_code)
                    <div class="qr-item">
                        <img src="{{ $navigation_qr_code }}" alt="Navigation QR Code">
                        <div class="qr-label">Online Access</div>
                    </div>
                    @endif
                </div>
            </div>

            <p><strong>Important Notes:</strong></p>
            <ul>
                <li>Please keep this receipt for your records</li>
                <li>Present your QR code at the conference for quick check-in</li>
                <li>If you have any questions, please contact our support team</li>
                <li>This receipt serves as proof of payment for your registration</li>
            </ul>

            <p>We look forward to seeing you at CPHIA 2025!</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>CPHIA 2025 Conference Team</strong></p>
            <p>For support, contact: <a href="mailto:{{ $support_email ?? 'support@cphia2025.com' }}">{{ $support_email ?? 'support@cphia2025.com' }}</a></p>
            <p>Visit our website: <a href="https://cphia2025.com">https://cphia2025.com</a></p>
            <p style="font-size: 12px; color: #adb5bd; margin-top: 15px;">
                This is an automated receipt. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>
