<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'PO_ID';

    /**
     * Tanggal yang akan dianggap sebagai instance Carbon
     */
    protected $dates = ['Order_Date', 'Expected_Delivery_Date'];

    /**
     * Tipe data cast otomatis
     */
    protected $casts = [
        'Order_Date' => 'datetime',
        'Expected_Delivery_Date' => 'datetime',
        'Status' => 'string',
        'Item_Quantity' => 'integer',
        'Item_Price' => 'decimal:2',
        'Total_Amount' => 'decimal:2',
    ];

    /**
     * Field yang boleh diisi secara massal
     */
    protected $fillable = [
        'Requisition_ID',
        'Supplier_ID',
        'Employee_ID',
        'Order_Date',
        'Expected_Delivery_Date',
        'Item_Name',
        'Item_Description',
        'Item_Quantity',
        'Item_Price',
        'Total_Amount',
        'Status',
        'Notes',
    ];

    /**
     * Relasi: Satu PO bisa memiliki banyak Invoice
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'PO_ID');
    }

    /**
     * Relasi: PO dimiliki oleh satu Purchase Requisition
     */
    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'Requisition_ID', 'Requisition_ID');
    }

    /**
     * Relasi: PO ditujukan ke satu Supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'Supplier_ID');
    }

    /**
     * Relasi: PO dibuat oleh satu Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'Employee_ID');
    }

    /**
     * Menghitung total biaya berdasarkan kuantitas dan harga
     *
     * @return float
     */
    public function calculateTotalCost()
    {
        return $this->Item_Quantity * $this->Item_Price;
    }

    /**
     * Override: Sebelum menyimpan, hitung Total_Amount otomatis
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if ($this->Item_Quantity && $this->Item_Price) {
            $this->Total_Amount = $this->calculateTotalCost();
        }

        return parent::save($options);
    }

    /**
     * Membuat invoice baru berdasarkan PO ini
     *
     * @return Invoice
     */
    public function createInvoice()
    {
        $invoice = new Invoice();
        $invoice->PO_ID = $this->PO_ID;
        $invoice->Invoice_Amount = $this->Total_Amount;
        $invoice->Due_Date = now()->addDays(30);
        $invoice->Payment_Status = 'Pending';
        $invoice->Invoice_Date = now();
        $invoice->save();

        // Tandai PO sebagai Completed jika belum
        if ($this->Status !== 'Completed') {
            $this->Status = 'Completed';
            $this->save();
        }

        return $invoice;
    }
}
