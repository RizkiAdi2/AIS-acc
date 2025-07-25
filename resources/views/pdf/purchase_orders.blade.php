<!-- filepath: d:\Real project\FInal AIS\erp-dashboard\resources\views\pdf\purchase_orders.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Orders Report</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px 0;
            background-color: #003366;
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
        h1 {
            text-align: center;
            font-size: 20px;
            margin: 25px 0;
            color: #003366;
        }
        .filter-summary {
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
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
        .total-section {
            text-align: right;
            margin: 20px 0;
        }
        .total-section strong {
            font-size: 14px;
        }
        .notes-section {
            margin: 20px 0;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
            border: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" alt="GlobalTech Solutions Logo">
        <div class="company-name">GlobalTech Solutions Inc.</div>
        <div class="company-tagline">"Innovating the Future, Today."</div>
        <div class="company-info">
            Jl. Ki Hajar Dewantara NO.1 Jawa barat<br>
            Email: info@globaltech.com | Phone: +62 8976267891<br>
            Website: www.globaltech.com
        </div>
    </div>

    <h1>Purchase Orders Report</h1>

    <div class="filter-summary">
        <strong>Report Parameters:</strong>
        Generated on {{ date('F d, Y h:i A') }}
        @if(isset($filters) && count($filters) > 0)
            | Filters: {{ implode(', ', $filters) }}
        @endif
    </div>

    <div class="summary-section">
        <div class="summary-title">Summary:</div>
        <div class="summary-item">Total POs: {{ count($data) }}</div>
        <div class="summary-item">Approved: {{ $data->where('Status', 'Approved')->count() }}</div>
        <div class="summary-item">Pending: {{ $data->where('Status', 'Pending')->count() }}</div>
        <div class="summary-item">Rejected: {{ $data->where('Status', 'Rejected')->count() }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>PO #</th>
                <th>Requisition #</th>
                <th>Supplier</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Unit Price (IDR)</th>
                <th>Total (IDR)</th>
                <th>Order Date</th>
                <th>Expected Delivery</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $po)
            <tr>
                <td>{{ $po->PO_ID }}</td>
                <td>{{ $po->purchaseRequisition ? $po->purchaseRequisition->Requisition_ID : 'Direct PO' }}</td>
                <td>{{ $po->supplier->Name ?? 'N/A' }}</td>
                <td>{{ $po->Item_Name }}</td>
                <td>{{ $po->Item_Quantity }}</td>
                <td>{{ number_format($po->Item_Price, 0, ',', '.') }}</td>
                <td>{{ number_format($po->Total_Amount, 0, ',', '.') }}</td>
                <td>{{ $po->Order_Date ? \Carbon\Carbon::parse($po->Order_Date)->format('d-m-Y') : 'N/A' }}</td>
                <td>{{ $po->Expected_Delivery_Date ? \Carbon\Carbon::parse($po->Expected_Delivery_Date)->format('d-m-Y') : 'N/A' }}</td>
                <td class="status-{{ strtolower($po->Status) }}">{{ $po->Status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <strong>Total PO Amount: IDR {{ number_format($data->sum('Total_Amount'), 0, ',', '.') }}</strong>
    </div>

    @if($data->count() === 1 && $data->first()->Notes)
    <div class="notes-section">
        <strong>Notes:</strong>
        <div>{{ $data->first()->Notes }}</div>
    </div>
    @endif

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