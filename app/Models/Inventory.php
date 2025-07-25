<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $primaryKey = 'Material_ID';

    protected $fillable = [
        'Material_Name',
        'Material_Description',
        'Material_Type',
        'Quantity_Available',
        'Unit_Of_Measurement',
        'Unit_Cost',
        'Reorder_Level',
        'Location',
        'Supplier_ID',
        'discount_type',
        'discount_value',
        'employee_id',
        'payment_method',
        'payment_term',
        'payment_date',
        'total_cost_after_discount',
    ];

    /**
     * Relationship with Supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'Supplier_ID');
    }

    /**
     * Relationship with Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Accessor: Hitung total cost setelah diskon
     */
    public function getTotalCostAfterDiscountAttribute()
    {
        $total = $this->Quantity_Available * $this->Unit_Cost;

        if ($this->discount_type === 'percentage' && $this->discount_value > 0) {
            return $total * (1 - ($this->discount_value / 100));
        }

        if ($this->discount_type === 'fixed' && $this->discount_value > 0) {
            return $total - $this->discount_value;
        }

        return $total;
    }

    /**
     * Tipe casting otomatis
     */
    protected $casts = [
        'payment_date' => 'datetime',
        'discount_value' => 'decimal:2',
        'Unit_Cost' => 'decimal:2',
        'total_cost_after_discount' => 'decimal:2',
    ];
}
