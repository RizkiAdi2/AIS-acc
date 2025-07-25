<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\PurchaseRequisition;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;
    
    public function mount(): void
{
    // Get the request from the container
    $request = app(Request::class);
    
    // Check if requisition_id is provided in the URL
    if ($request->has('requisition_id')) {
        $requisitionId = $request->requisition_id;
        $requisition = PurchaseRequisition::find($requisitionId);
        
        if ($requisition) {
            $this->form->fill([
                'Requisition_ID' => $requisitionId,
                'is_direct_po' => false,
                'Supplier_ID' => $requisition->Supplier_ID,
                'Employee_ID' => $requisition->Employee_ID,
                'Expected_Delivery_Date' => $requisition->Expected_Delivery_Date,
                'Item_Name' => $requisition->Item_Name,
                'Item_Description' => $requisition->Description,
                'Item_Quantity' => $requisition->Item_Quantity,
                'Item_Price' => $requisition->Item_Price,
                'Total_Amount' => $requisition->Total_Cost,
                'Notes' => 'Created from Purchase Requisition #' . $requisition->Requisition_ID,
            ]);
        }
    }
    
    parent::mount();
}
    
    protected function beforeCreate(): void
    {
        // Validate that if a requisition is selected, it's in the appropriate status
        $data = $this->form->getState();
        
        if (isset($data['Requisition_ID']) && $data['Requisition_ID']) {
            $requisition = PurchaseRequisition::find($data['Requisition_ID']);
            
            if (!$requisition) {
                $this->halt();
                Notification::make()
                    ->title('Invalid Requisition')
                    ->body('The selected requisition does not exist.')
                    ->danger()
                    ->send();
            }
            
            if ($requisition && $requisition->Status === 'Rejected') {
                $this->halt();
                Notification::make()
                    ->title('Invalid Requisition')
                    ->body('Cannot create PO from a rejected requisition.')
                    ->danger()
                    ->send();
            }
        }
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If creating from requisition, populate data from the requisition
        if (isset($data['Requisition_ID']) && $data['Requisition_ID']) {
            $requisition = PurchaseRequisition::find($data['Requisition_ID']);
            if ($requisition) {
                // Auto-populate fields from requisition
                $data['Item_Name'] = $data['Item_Name'] ?? $requisition->Item_Name;
                $data['Item_Quantity'] = $data['Item_Quantity'] ?? $requisition->Item_Quantity;
                $data['Item_Price'] = $data['Item_Price'] ?? $requisition->Item_Price;
                $data['Expected_Delivery_Date'] = $data['Expected_Delivery_Date'] ?? $requisition->Expected_Delivery_Date;
                $data['Supplier_ID'] = $data['Supplier_ID'] ?? $requisition->Supplier_ID;
                // Calculate total amount
                $data['Total_Amount'] = $data['Item_Quantity'] * $data['Item_Price'];
            }
        }

        // If this is a direct PO (no requisition), set Requisition_ID to null
        if (isset($data['is_direct_po']) && $data['is_direct_po']) {
            $data['Requisition_ID'] = null;
        }
        
        // Remove the is_direct_po field as it's not part of the model
        unset($data['is_direct_po']);
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        // Get the created record
        $record = $this->record;
        
        // If this PO was created from a requisition, update the requisition status
        if ($record->Requisition_ID) {
            $requisition = PurchaseRequisition::find($record->Requisition_ID);
            if ($requisition && $requisition->Status !== 'Approved') {
                $requisition->Status = 'Approved';
                $requisition->save();
                
                // Show notification
                Notification::make()
                    ->title('Requisition Approved')
                    ->body('Purchase Requisition #' . $requisition->Requisition_ID . ' has been approved.')
                    ->success()
                    ->send();
            }
        } else {
            // This is a direct PO
            Notification::make()
                ->title('Direct Purchase Order Created')
                ->body('Purchase Order #' . $record->PO_ID . ' has been created successfully.')
                ->success()
                ->send();
        }
        
        // Option to create invoice automatically for approved POs
        // if ($record->Status === 'Approved') {
        //     try {
        //         $invoice = $record->createInvoice();
                
        //         Notification::make()
        //             ->title('Invoice Created')
        //             ->body('Invoice #' . $invoice->Invoice_ID . ' has been created for this PO.')
        //             ->success()
        //             ->send();
        //     } catch (\Exception $e) {
        //         Notification::make()
        //             ->title('Invoice Creation Failed')
        //             ->body('Could not create invoice: ' . $e->getMessage())
        //             ->danger()
        //             ->send();
        //     }
        // }
    }
}