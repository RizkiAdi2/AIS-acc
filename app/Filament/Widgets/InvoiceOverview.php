<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class InvoiceOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total Invoices', Invoice::count()),

            Card::make('Unpaid Invoices', Invoice::where('Payment_Status', 'pending')->count())
                ->description('Invoices waiting for payment')
                ->color('danger'),

            Card::make('Paid Invoices', Invoice::where('Payment_Status', 'paid')->count())
                ->color('success'),
        ];
    }
}
