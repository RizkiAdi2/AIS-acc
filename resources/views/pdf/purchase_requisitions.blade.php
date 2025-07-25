<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Requisitions Report</title>
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

        /* Filter Summary */
        .filter-summary {
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Table Section */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th {
            background-color: #f4f4f4;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            color: #003366;
        }

        td {
            padding: 8px;
            text-align: left;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .status-approved {
            color: #28a745;
            font-weight: bold;
        }

        .status-rejected {
            color: #dc3545;
            font-weight: bold;
        }

        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }

        /* Summary Section */
        .summary-section {
            margin: 20px 0;
            padding: 10px;
            background-color: #f4f4f4;
            border-radius: 4px;
        }

        .summary-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #003366;
        }

        .summary-item {
            display: inline-block;
            margin-right: 20px;
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

        /* Page break utility */
        .page-break {
            page-break-after: always;
        }

        /* Signatures section */
        .signatures {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 45%;
            border-top: 1px solid #000;
            padding-top: 5px;
            text-align: center;
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

    <h1>Purchase Requisitions Report</h1>
    
    <!-- Filter Summary - Optional, can be populated dynamically -->
    <div class="filter-summary">
        <strong>Report Parameters:</strong> 
        Generated on {{ date('F d, Y h:i A') }}
        @if(isset($filters) && count($filters) > 0)
            | Filters: {{ implode(', ', $filters) }}
        @endif
    </div>

    <!-- Summary Statistics - Optional -->
    <div class="summary-section">
        <div class="summary-title">Summary:</div>
        <div class="summary-item">Total Requisitions: {{ count($data) }}</div>
        <div class="summary-item">Approved: {{ $data->where('Status', 'Approved')->count() }}</div>
        <div class="summary-item">Pending: {{ $data->where('Status', 'Pending')->count() }}</div>
        <div class="summary-item">Rejected: {{ $data->where('Status', 'Rejected')->count() }}</div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Req #</th>
                <th>Employee</th>
                <th>Department</th>
                <th>Item Name</th>
                <th>Qty</th>
                <th>Price (IDR)</th>
                <th>Total (IDR)</th>
                <th>Expected Delivery</th>
                <th>Status</th>
                <th>Date Requested</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $item)
                <tr>
                    <td>{{ $item->Requisition_ID }}</td>
                    <td>{{ $item->employee->name ?? 'N/A' }}</td>
                    <td>{{ $item->Department }}</td>
                    <td>{{ $item->Item_Name }}</td>
                    <td>{{ $item->Item_Quantity }}</td>
                    <td>{{ number_format($item->Item_Price, 0, ',', '.') }}</td>
                    <td>{{ number_format($item->Total_Cost, 0, ',', '.') }}</td>
                    <td>{{ $item->Expected_Delivery_Date ? date('d-m-Y', strtotime($item->Expected_Delivery_Date)) : 'N/A' }}</td>
                    <td class="status-{{ strtolower($item->Status) }}">{{ $item->Status }}</td>
                    <td>{{ $item->Date_Requested ? date('d-m-Y', strtotime($item->Date_Requested)) : 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total Amount -->
    <div style="text-align: right; margin: 20px 0;">
        <strong>Total Requisition Amount: IDR {{ number_format($data->sum('Total_Cost'), 0, ',', '.') }}</strong>
    </div>

    <!-- Signatures Section -->
   <div class="signatures" style="margin-top:40px; display: flex; flex-direction: row; justify-content: space-between;">
        <div class="signature-box" style="width:30%; text-align:center;">
            <div style="height:60px;"></div>
            <div style="border-top:1px solid #000; margin-top:10px; padding-top:5px;">Prepared By</div>
        </div>
        <div style="width:30%;"></div> <!-- Spacer for separation -->
        <div class="signature-box" style="width:30%; text-align:center;">
            <div style="height:60px;"></div>
            <div style="border-top:1px solid #000; margin-top:10px; padding-top:5px;">Approved By</div>
        </div>
    </div>


    <div class="footer">
        <div>Report generated on {{ date('d-m-Y H:i:s') }}</div>
        <div class="contact-info">For inquiries, please contact info@globaltech.com or call +62 8976267891</div>
        <div>© {{ date('Y') }} GlobalTech Solutions Inc. All rights reserved.</div>
    </div>
</body>
</html>