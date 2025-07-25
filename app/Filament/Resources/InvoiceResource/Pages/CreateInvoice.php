<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Resources\Pages\CreateRecord;

use Filament\Notifications\Notification;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['Invoice_Number'])) {
            $data['Invoice_Number'] = $this->generateInvoiceNumber();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Invoice Created')
            ->body('Invoice ' . $this->record->Invoice_Number . ' has been successfully created.')
            ->success()
            ->send();
    }

    /**
     * Generate automatic invoice number
     * Format: INV-YYYYMM-XXXX (e.g., INV-202505-0001)
     */
    private function generateInvoiceNumber(): string
    {
        $currentMonth = now()->format('Ym'); // 202505
        $prefix = 'INV-' . $currentMonth . '-';

        $lastInvoice = \App\Models\Invoice::where('Invoice_Number', 'like', $prefix . '%')
            ->orderBy('Invoice_Number', 'desc')
            ->first();

        $nextNumber = $lastInvoice
            ? ((int) substr($lastInvoice->Invoice_Number, -4)) + 1
            : 1;

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

