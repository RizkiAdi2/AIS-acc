<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';
    protected $primaryKey = 'Employee_ID';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'Name',
        'identification_number',
        'gender',
        'Department',
        'Role',
        'Phone',
        'Email',
        'Address',
        'hire_date',
        'Status',
        'salary',
    ];

    protected $casts = [
        'salary' => 'decimal:2',
        'hire_date' => 'date', // memastikan date casting
    ];

    public function purchaseRequisitions()
    {
        return $this->hasMany(PurchaseRequisition::class, 'Employee_ID');
    }
}
