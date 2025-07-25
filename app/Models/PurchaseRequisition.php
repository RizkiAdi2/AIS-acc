<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PurchaseOrder;


class PurchaseRequisition extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_requisitions';
    
    protected $primaryKey = 'Requisition_ID';
    
    public $incrementing = true;
    
    protected $fillable = [
        'Employee_ID',
        'Department',
        'Date_Requested',
        'Description',
        'Item_Name',
        'Item_Quantity',
        'Item_Price',
        'Expected_Delivery_Date',
        'Total_Cost',
        'Status',
        'Supplier_ID'
    ];
    
    protected $casts = [
        'Date_Requested' => 'date',
        'Expected_Delivery_Date' => 'date',
        'Item_Quantity' => 'integer',
        'Item_Price' => 'decimal:2',
        'Total_Cost' => 'decimal:2',
    ];
    
    // Relationships
   public function employee()
    {
        return $this->belongsTo(Employee::class, 'Employee_ID', 'Employee_ID');
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'Supplier_ID', 'Supplier_ID');
    }
    
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'Requisition_ID', 'Requisition_ID');
    }
    
    // Helper method untuk auto-create PO
    public function createPurchaseOrder()
    {
        // Pastikan sudah diapprove dan belum ada PO yang dibuat
        if ($this->Status !== 'Approved' || $this->purchaseOrders()->exists()) {
            return false;
        }
        
        // Create the purchase order
        $purchaseOrder = new PurchaseOrder();
        $purchaseOrder->Requisition_ID = $this->Requisition_ID;
        $purchaseOrder->Supplier_ID = $this->Supplier_ID;
        $purchaseOrder->Employee_ID = $this->Employee_ID;
        $purchaseOrder->Order_Date = now();
        $purchaseOrder->Expected_Delivery_Date = $this->Expected_Delivery_Date;
        $purchaseOrder->Item_Name = $this->Item_Name;
        $purchaseOrder->Item_Description = $this->Description;
        $purchaseOrder->Item_Quantity = $this->Item_Quantity;
        $purchaseOrder->Item_Price = $this->Item_Price;
        $purchaseOrder->Total_Amount = $this->Total_Cost;
        $purchaseOrder->Status = 'Pending';
        $purchaseOrder->Notes = "Automatically created from Purchase Requisition #{$this->Requisition_ID}\nDepartment: {$this->Department}";
        $purchaseOrder->save();
        
        return $purchaseOrder;
    }
    
    // Accessor
    public function getStatusColorAttribute()
    {
        return match($this->Status) {
            'Approved' => 'success',
            'Rejected' => 'danger',
            'Pending' => 'warning',
            default => 'secondary',
        };
    }
    
    // Boot method untuk melakukan kalkulasi otomatis
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($requisition) {
            // Auto-calculate total cost if not set
            if (empty($requisition->Total_Cost) && !empty($requisition->Item_Quantity) && !empty($requisition->Item_Price)) {
                $requisition->Total_Cost = $requisition->Item_Quantity * $requisition->Item_Price;
            }
        });
        
        static::updating(function ($requisition) {
            // Auto-calculate total cost when updating
            if (!empty($requisition->Item_Quantity) && !empty($requisition->Item_Price)) {
                $requisition->Total_Cost = $requisition->Item_Quantity * $requisition->Item_Price;
            }
            
            // Auto-create PO when approved with checkbox
            if ($requisition->isDirty('Status') && $requisition->Status === 'Approved' && $requisition->approve_and_create_po) {
                // Schedule PO creation after save
                $requisition->afterCommit(function () use ($requisition) {
                    $requisition->createPurchaseOrder();
                });
            }
        });
    }
}