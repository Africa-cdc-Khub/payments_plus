<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Registration Receipt - CPHIA 2025</title>
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
        .participants-section {
            margin: 30px 0;
        }
        .participants-title {
            color: #1a5632;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .participant-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #1a5632;
        }
        .participant-name {
            font-weight: 600;
            color: #1a5632;
            margin-bottom: 8px;
        }
        .participant-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            font-size: 14px;
        }
        .participant-detail {
            color: #6c757d;
        }
        .participant-detail strong {
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
            .participant-details {
                grid-template-columns: 1fr;
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
            <h2 class="receipt-title">Group Registration Receipt</h2>
            
            <p>Dear {{ $focal_person_name ?? 'Group Coordinator' }},</p>
            
            <p>Thank you for your group registration for CPHIA 2025. This is your official receipt confirming your payment and registration details for all group members.</p>

            <!-- Receipt Details -->
            <div class="receipt-details">
                <div class="detail-row">
                    <span class="detail-label">Registration ID:</span>
                    <span class="detail-value">#{{ $registration_id ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Group Coordinator:</span>
                    <span class="detail-value">{{ $focal_person_name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Coordinator Email:</span>
                    <span class="detail-value">{{ $focal_person_email ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Package:</span>
                    <span class="detail-value">{{ $package_name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Number of Participants:</span>
                    <span class="detail-value">{{ $participants_count ?? 0 }}</span>
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

            <!-- Participants Section -->
            <div class="participants-section">
                <h3 class="participants-title">Group Participants</h3>
                
                @if(isset($participants) && is_array($participants))
                    @foreach($participants as $index => $participant)
                    <div class="participant-card">
                        <div class="participant-name">{{ $participant['name'] ?? 'Participant ' . ($index + 1) }}</div>
                        <div class="participant-details">
                            <div class="participant-detail">
                                <strong>Email:</strong> {{ $participant['email'] ?? 'N/A' }}
                            </div>
                            <div class="participant-detail">
                                <strong>Phone:</strong> {{ $participant['phone'] ?? 'N/A' }}
                            </div>
                            <div class="participant-detail">
                                <strong>Organization:</strong> {{ $participant['organization'] ?? 'N/A' }}
                            </div>
                            @if(isset($participant['institution']) && $participant['institution'])
                            <div class="participant-detail">
                                <strong>Institution:</strong> {{ $participant['institution'] }}
                            </div>
                            @endif
                            <div class="participant-detail">
                                <strong>Nationality:</strong> {{ $participant['nationality'] ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p>No participants found.</p>
                @endif
            </div>

            <!-- QR Codes Section -->
            <div class="qr-section">
                <h3 style="color: #1a5632; margin-bottom: 20px;">Registration QR Codes</h3>
                <p style="color: #6c757d; margin-bottom: 20px;">Please save these QR codes for easy access to your group registration and conference information.</p>
                
                <div class="qr-codes">
                    @if(isset($qr_codes) && is_array($qr_codes) && count($qr_codes) > 0)
                        @foreach($qr_codes as $index => $qrCode)
                        <div class="qr-item">
                            <img src="{{ $qrCode }}" alt="Registration QR Code {{ $index + 1 }}">
                            <div class="qr-label">Participant {{ $index + 1 }}</div>
                        </div>
                        @endforeach
                    @else
                        <div class="qr-item">
                            <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTUwIiBoZWlnaHQ9IjE1MCIgZmlsbD0iI2Y4ZjlmYSIgc3Ryb2tlPSIjY2NjIiBzdHJva2Utd2lkdGg9IjIiLz48dGV4dCB4PSI3NSIgeT0iNzUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiM2NjYiIGZvbnQtc2l6ZT0iMTIiPlFSIENvZGU8L3RleHQ+PC9zdmc+" alt="QR Code Placeholder">
                            <div class="qr-label">Group Registration</div>
                        </div>
                    @endif
                </div>
            </div>

            <p><strong>Important Notes:</strong></p>
            <ul>
                <li>Please keep this receipt for your records</li>
                <li>Each participant should present their QR code at the conference for quick check-in</li>
                <li>As the group coordinator, you are responsible for ensuring all participants are aware of conference details</li>
                <li>If you have any questions, please contact our support team</li>
                <li>This receipt serves as proof of payment for your group registration</li>
            </ul>

            <p>We look forward to seeing your group at CPHIA 2025!</p>
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
