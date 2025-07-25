<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payments Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 12px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .summary-section {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            margin-bottom: 30px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .summary-label {
            font-weight: bold;
            color: #374151;
        }
        
        .summary-value {
            font-weight: bold;
            color: #111827;
        }
        
        .summary-value.amount {
            color: #059669;
        }
        
        .summary-value.outstanding {
            color: #dc2626;
        }
        
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }
        
        .payments-table th {
            background-color: #2563eb;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        
        .payments-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        
        .payments-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .payments-table tr:hover {
            background-color: #f3f4f6;
        }
        
        .amount-cell {
            text-align: right;
            font-weight: bold;
        }
        
        .amount-positive {
            color: #059669;
        }
        
        .amount-negative {
            color: #dc2626;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 9px;
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
        
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }

        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            
            .payments-table {
                font-size: 9px;
            }
            
            .payments-table th {
                font-size: 9px;
                padding: 8px 6px;
            }
            
            .payments-table td {
                padding: 6px 6px;
                font-size: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PAYMENTS REPORT</h1>
        <p>Report Generated: {{ $generated_at }}</p>
        <p>Total Records: {{ $summary['total_records'] }}</p>
    </div>

    <div class="summary-section">
        <h3 style="margin-top: 0; color: #2563eb;">Summary</h3>
        <div class="summary-grid">
            <div>
                <div class="summary-item">
                    <span class="summary-label">Total Records:</span>
                    <span class="summary-value">{{ number_format($summary['total_records']) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Invoice Amount:</span>
                    <span class="summary-value amount">IDR {{ number_format($summary['total_invoice_amount'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div>
                <div class="summary-item">
                    <span class="summary-label">Total Amount Paid:</span>
                    <span class="summary-value amount">IDR {{ number_format($summary['total_amount_paid'], 0, ',', '.') }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Outstanding:</span>
                    <span class="summary-value outstanding">IDR {{ number_format($summary['total_outstanding'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <table class="payments-table">
        <thead>
            <tr>
                <th style="width: 8%;">Invoice #</th>
                <th style="width: 8%;">PO #</th>
                <th style="width: 15%;">Item Name</th>
                <th style="width: 10%;">Payment Date</th>
                <th style="width: 8%;">Method</th>
                <th style="width: 12%;">Invoice Amount</th>
                <th style="width: 12%;">Amount Paid</th>
                <th style="width: 12%;">Outstanding</th>
                <th style="width: 8%;">Due Date</th>
                <th style="width: 7%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $paymentData)
                @php
                    $payment = $paymentData['payment'];
                    $outstanding = $paymentData['outstanding'];
                    $status = $payment->invoice?->Payment_Status;
                    $statusClass = match($status) {
                        'Paid' => 'status-paid',
                        'Partial' => 'status-partial',
                        default => 'status-unpaid'
                    };
                @endphp
                <tr>
                    <td>{{ $payment->invoice?->Invoice_Number ?? 'N/A' }}</td>
                    <td>{{ $payment->invoice?->purchaseOrder?->PO_ID ?? 'N/A' }}</td>
                    <td>{{ Str::limit($payment->invoice?->purchaseOrder?->Item_Name ?? 'N/A', 25) }}</td>
                    <td>{{ $payment->Payment_Date?->format('d M Y') ?? 'N/A' }}</td>
                    <td>{{ $payment->Payment_Method ?? 'N/A' }}</td>
                    <td class="amount-cell">IDR {{ number_format($payment->invoice?->Invoice_Amount ?? 0, 0, ',', '.') }}</td>
                    <td class="amount-cell amount-positive">IDR {{ number_format($payment->Amount_Paid ?? 0, 0, ',', '.') }}</td>
                    <td class="amount-cell {{ $outstanding > 0 ? 'amount-negative' : 'amount-positive' }}">
                        IDR {{ number_format($outstanding, 0, ',', '.') }}
                    </td>
                    <td>{{ $payment->invoice?->Due_Date?->format('d M Y') ?? 'N/A' }}</td>
                    <td>
                        <span class="status-badge {{ $statusClass }}">{{ $status ?? 'Unknown' }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p><strong>End of Report</strong></p>
        <p>This report contains {{ $summary['total_records'] }} payment record(s)</p>
        <p>Generated automatically by the Finance Management System</p>
    </div>
</body>
</html>