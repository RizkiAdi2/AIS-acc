<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $primaryKey = 'Invoice_ID';

    protected $fillable = [
        'Invoice_Number',
        'PO_ID',
        'Invoice_Amount',
        'Tax',
        'Payment_Method',
        'Due_Date',
        'Payment_Status',
        'Invoice_Date',
        'Attachment',
        'Notes',
    ];

    protected $casts = [
        'Invoice_Date' => 'date',
        'Due_Date' => 'date',
        'Invoice_Amount' => 'decimal:2',
        'Tax' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'PO_ID');
    }

    protected static function booted()
    {
        static::creating(function ($invoice) {
            // Generate invoice number if not set
            if (empty($invoice->Invoice_Number)) {
                $invoice->Invoice_Number = static::generateInvoiceNumber();
            }

            // Auto-fill invoice amount from PO if empty
            if (empty($invoice->Invoice_Amount) && $invoice->PO_ID) {
                $invoice->Invoice_Amount = optional($invoice->purchaseOrder)->Total_Amount;
            }
        });
    }

    /**
     * Generate automatic invoice number
     * Format: INV-YYYYMM-XXXX (e.g., INV-202505-0001)
     */
    public static function generateInvoiceNumber(): string
    {
        $currentMonth = now()->format('Ym'); // 202505
        $prefix = 'INV-' . $currentMonth . '-';
        
        // Get the last invoice number for current month
        $lastInvoice = static::where('Invoice_Number', 'like', $prefix . '%')
            ->orderBy('Invoice_Number', 'desc')
            ->first();
        
        if ($lastInvoice) {
            // Extract the sequential number and increment it
            $lastNumber = (int) substr($lastInvoice->Invoice_Number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            // First invoice of the month
            $nextNumber = 1;
        }
        
        // Format with leading zeros (4 digits)
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}