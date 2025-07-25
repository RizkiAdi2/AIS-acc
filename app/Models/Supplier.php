<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers'; // Specify the table name if it's different from the model name
    protected $primaryKey = 'Supplier_ID'; // Primary key field name

    protected $fillable = [
        'Name', 'Contact_Email', 'Phone_Number', 'Address', 'Supplier_Type','Country','State','Payment_Terms','Product_Service_Type'
    ];

    // If you want to work with custom timestamps
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
}

