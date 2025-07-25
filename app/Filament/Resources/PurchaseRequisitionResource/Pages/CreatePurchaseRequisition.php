<?php

namespace App\Filament\Resources\PurchaseRequisitionResource\Pages;

use App\Filament\Resources\PurchaseRequisitionResource;
use App\Models\PurchaseOrder;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePurchaseRequisition extends CreateRecord
{
    protected static string $resource = PurchaseRequisitionResource::class;

    protected function afterCreate(): void
    {
        // Get the created record
        $record = $this->record;
        
        // Get the form data
        $data = $this->data;
        
        // Check if the approve checkbox was checked
        if (isset($data['approve_and_create_po']) && $data['approve_and_create_po']) {
            // Update status to Approved
            $record->Status = 'Approved';
            $record->save();
            
            // Create a new Purchase Order
            $purchaseOrder = new PurchaseOrder();
            $purchaseOrder->Requisition_ID = $record->Requisition_ID;
            $purchaseOrder->Supplier_ID = $data['Supplier_ID'];
            $purchaseOrder->Order_Date = now();
            $purchaseOrder->Expected_Delivery_Date = $record->Expected_Delivery_Date;
            $purchaseOrder->Total_Amount = $record->Total_Cost;
            $purchaseOrder->Status = 'Pending';
            $purchaseOrder->Notes = 'Automatically created from Purchase Requisition #' . $record->Requisition_ID;
            $purchaseOrder->save();
            
            // Show notification
            Notification::make()
                ->title('Purchase Order Created')
                ->body('Purchase Order #' . $purchaseOrder->PO_ID . ' has been created successfully.')
                ->success()
                ->send();
        }
    }
}