<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $primaryKey = 'Payment_ID';

    protected $fillable = [
        'Invoice_ID',
        'Amount_Paid',
        'Payment_Date',
        'Payment_Method',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'Invoice_ID');
    }
}
