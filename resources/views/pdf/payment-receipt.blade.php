<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px 0;
            background-color: #003366; /* Corporate blue */
            color: white;
        }

        .header img {
            max-width: 120px;
            height: auto;
            display: block;
            margin: 0 auto 10px;
        }

        .header .company-name {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header .company-tagline {
            font-style: italic;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .header .company-info {
            font-size: 12px;
        }

        /* Main Title */
        h1 {
            text-align: center;
            font-size: 20px;
            margin: 25px 0;
            color: #003366;
        }

        /* Receipt Info Summary */
        .receipt-summary {
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Information Tables */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .info-table, .info-table th, .info-table td {
            border: 1px solid #ddd;
        }

        .info-table th {
            background-color: #f4f4f4;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            color: #003366;
            width: 30%;
        }

        .info-table td {
            padding: 8px;
            text-align: left;
        }

        .info-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Amount Summary Section */
        .amount-summary {
            margin: 20px 0;
            padding: 10px;
            background-color: #f4f4f4;
            border-radius: 4px;
        }

        .amount-summary-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #003366;
            font-size: 14px;
        }

        .amount-table {
            width: 100%;
            border-collapse: collapse;
        }

        .amount-table, .amount-table th, .amount-table td {
            border: 1px solid #ddd;
        }

        .amount-table th {
            background-color: #003366;
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 8px;
        }

        .amount-table td {
            padding: 8px;
            text-align: right;
        }

        .amount-table .total-row {
            background-color: #e6f2ff;
            font-weight: bold;
            color: #003366;
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-partial {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-unpaid {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .paid {
            color: #059669;
            font-weight: bold;
        }

        .outstanding {
            color: #dc2626;
            font-weight: bold;
        }

        /* Signatures section */
        .signatures {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 30%;
            text-align: center;
        }

        .signature-box div:first-child {
            height: 60px;
        }

        .signature-box div:last-child {
            border-top: 1px solid #000;
            margin-top: 10px;
            padding-top: 5px;
        }

        /* Footer Section */
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }

        .footer .contact-info {
            margin-top: 5px;
        }

        /* Print styles */
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <!-- Company Logo -->
        <img src="{{ public_path('images/logo.png') }}" alt="GlobalTech Solutions Logo">
        
        <!-- Company Details -->
        <div class="company-name">GlobalTech Solutions Inc.</div>
        <div class="company-tagline">"Innovating the Future, Today."</div>
        <div class="company-info">
            Jl. Ki Hajar Dewantara NO.1 Jawa barat<br>
            Email: info@globaltech.com | Phone: +62 8976267891<br>
            Website: www.globaltech.com
        </div>
    </div>

    <h1>Payment Receipt</h1>
    
    <!-- Receipt Summary -->
    <div class="receipt-summary">
        <strong>Receipt Information:</strong> 
        Receipt #: {{ $payment->Payment_ID ?? 'N/A' }} | 
        Generated on {{ $generated_at }}
    </div>

    <!-- Invoice Information Table -->
    <table class="info-table">
        <thead>
            <tr>
                <th colspan="2" style="background-color: #003366; color: white; text-align: center;">Invoice Information</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>Invoice Number:</th>
                <td>{{ $payment->invoice?->Invoice_Number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>PO Number:</th>
                <td>{{ $payment->invoice?->purchaseOrder?->PO_ID ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Item Name:</th>
                <td>{{ $payment->invoice?->purchaseOrder?->Item_Name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Due Date:</th>
                <td>
                    {{ $payment->invoice?->Due_Date ? \Carbon\Carbon::parse($payment->invoice->Due_Date)->format('d M Y') : 'N/A' }}
                </td>
            </tr>
            <tr>
                <th>Status:</th>
                <td>
                    @php
                        $status = $payment->invoice?->Payment_Status;
                        $statusClass = match($status) {
                            'Paid' => 'status-paid',
                            'Partial' => 'status-partial',
                            default => 'status-unpaid'
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $status ?? 'Unknown' }}</span>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Payment Details Table -->
    <table class="info-table">
        <thead>
            <tr>
                <th colspan="2" style="background-color: #003366; color: white; text-align: center;">Payment Details</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>Payment Date:</th>
                <td>
                    {{ $payment->Payment_Date ? \Carbon\Carbon::parse($payment->Payment_Date)->format('d M Y') : 'N/A' }}
                </td>
            </tr>
            <tr>
                <th>Payment Method:</th>
                <td>{{ $payment->Payment_Method ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Amount Paid:</th>
                <td><span class="paid">IDR {{ number_format($payment->Amount_Paid ?? 0, 0, ',', '.') }}</span></td>
            </tr>
        </tbody>
    </table>

    <!-- Amount Summary Section -->
    <div class="amount-summary">
        <div class="amount-summary-title">Payment Summary:</div>
        <table class="amount-table">
            <thead>
                <tr>
                    <th style="text-align: left;">Description</th>
                    <th style="text-align: right;">Amount (IDR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: left; padding-left: 8px;">Invoice Amount</td>
                    <td>{{ number_format($payment->invoice?->Invoice_Amount ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="text-align: left; padding-left: 8px;">Amount Paid (This Payment)</td>
                    <td><span class="paid">{{ number_format($payment->Amount_Paid ?? 0, 0, ',', '.') }}</span></td>
                </tr>
                <tr>
                    <td style="text-align: left; padding-left: 8px;">Total Paid</td>
                    <td><span class="paid">{{ number_format(($payment->invoice?->Invoice_Amount ?? 0) - $outstanding, 0, ',', '.') }}</span></td>
                </tr>
                <tr class="total-row">
                    <td style="text-align: left; padding-left: 8px;"><strong>Outstanding Balance</strong></td>
                    <td><strong><span class="outstanding">{{ number_format($outstanding, 0, ',', '.') }}</span></strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Signatures Section -->
    <div class="signatures">
        <div class="signature-box">
            <div></div>
            <div>Received By</div>
        </div>
        <div style="width: 30%;"></div> <!-- Spacer -->
        <div class="signature-box">
            <div></div>
            <div>Authorized By</div>
        </div>
    </div>

    <div class="footer">
        <div><strong>Thank you for your payment!</strong></div>
        <div>Receipt generated on {{ date('d-m-Y H:i:s') }}</div>
        <div class="contact-info">This is an automatically generated receipt. Please keep this for your records.</div>
        <div class="contact-info">For inquiries, please contact info@globaltech.com or call +62 8976267891</div>
        <div>© {{ date('Y') }} GlobalTech Solutions Inc. All rights reserved.</div>
    </div>
</body>
</html>