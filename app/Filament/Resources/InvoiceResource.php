<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationGroup = 'Finance Management';
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('Invoice_Number')
                ->label('Invoice Number')
                ->default(fn () => self::generateInvoiceNumber())
                ->disabled()
                ->dehydrated()
                ->required(),

            Forms\Components\Select::make('PO_ID')
                ->label('Purchase Order')
                ->options(fn () => PurchaseOrder::all()->mapWithKeys(fn ($po) => [
                    $po->PO_ID => 'PO-' . $po->PO_ID . ' | ' . $po->Item_Name,
                ]))
                ->searchable()
                ->preload()
                ->reactive()
                ->required()
                ->afterStateUpdated(function ($state, $set) {
                    // Auto-fill Invoice_Amount when PO is selected
                    if ($state) {
                        $po = PurchaseOrder::find($state);
                        if ($po) {
                            $set('Invoice_Amount', $po->Total_Amount);
                        }
                    }
                }),

            Forms\Components\Grid::make(2)->schema([
                Placeholder::make('PO_ID_display')
                    ->label('PO Number')
                    ->content(fn ($get) => $get('PO_ID') ? 'PO-' . $get('PO_ID') : '-'),

                Placeholder::make('Item_Name_display')
                    ->label('Item Name')
                    ->content(fn ($get) => optional(PurchaseOrder::find($get('PO_ID')))?->Item_Name ?? '-'),

                Placeholder::make('Item_Description_display')
                    ->label('Description')
                    ->content(fn ($get) => optional(PurchaseOrder::find($get('PO_ID')))?->Item_Description ?? '-'),

                Placeholder::make('Item_Quantity_display')
                    ->label('Quantity')
                    ->content(fn ($get) => optional(PurchaseOrder::find($get('PO_ID')))?->Item_Quantity . ' pcs'),

                Placeholder::make('Item_Price_display')
                    ->label('Unit Price')
                    ->content(fn ($get) => number_format(optional(PurchaseOrder::find($get('PO_ID')))?->Item_Price ?? 0, 0, ',', '.') . ' IDR'),

                Placeholder::make('Total_Amount_display')
                    ->label('Total Amount')
                    ->content(fn ($get) => number_format(optional(PurchaseOrder::find($get('PO_ID')))?->Total_Amount ?? 0, 0, ',', '.') . ' IDR'),

                Placeholder::make('Order_Date_display')
                    ->label('Order Date')
                    ->content(fn ($get) => optional(PurchaseOrder::find($get('PO_ID')))?->Order_Date?->format('d M Y') ?? '-'),

                Placeholder::make('Expected_Delivery_Date_display')
                    ->label('Expected Delivery')
                    ->content(fn ($get) => optional(PurchaseOrder::find($get('PO_ID')))?->Expected_Delivery_Date?->format('d M Y') ?? '-'),

                Placeholder::make('Status_display')
                    ->label('PO Status')
                    ->content(fn ($get) => optional(PurchaseOrder::find($get('PO_ID')))?->Status ?? '-'),

                Placeholder::make('Supplier_display')
                    ->label('Supplier')
                    ->content(fn ($get) => optional(optional(PurchaseOrder::with('supplier')->find($get('PO_ID')))?->supplier)?->Name ?? '-'),

                Placeholder::make('Employee_display')
                    ->label('Created By')
                    ->content(fn ($get) => optional(optional(PurchaseOrder::with('employee')->find($get('PO_ID')))?->employee)?->Name ?? '-'),

                Placeholder::make('Notes_display')
                    ->label('Notes')
                    ->content(fn ($get) => optional(PurchaseOrder::find($get('PO_ID')))?->Notes ?? '-'),
            ])
            ->visible(fn ($get) => $get('PO_ID') !== null),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('Invoice_Amount')
                    ->label('Invoice Amount')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('Tax')
                    ->label('PPN (11%)')
                    ->numeric()
                    ->default(0),

                Forms\Components\Select::make('Payment_Method')
                    ->label('Payment Method')
                    ->options([
                        'Bank Transfer' => 'Bank Transfer',
                        'Cash' => 'Cash',
                        'Cheque' => 'Cheque',
                        'QRIS' => 'QRIS',
                    ])
                    ->required(),

                Forms\Components\Select::make('Payment_Status')
                    ->label('Payment Status')
                    ->options([
                        'Paid' => 'Paid',
                        'Pending' => 'Pending',
                    ])
                    ->required(),

                Forms\Components\DatePicker::make('Invoice_Date')
                    ->label('Invoice Date')
                    ->required(),

                Forms\Components\DatePicker::make('Due_Date')
                    ->label('Due Date')
                    ->default(now()->addDays(30))
                    ->required(),
            ]),

            Forms\Components\FileUpload::make('Attachment')
                ->label('Invoice Attachment (PDF)')
                ->directory('invoices')
                ->acceptedFileTypes(['application/pdf'])
                ->maxSize(2048),

            Forms\Components\Textarea::make('Notes')
                ->label('Additional Notes')
                ->rows(3)
                ->maxLength(500),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('Invoice_Number')->label('Invoice #')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('purchaseOrder.PO_ID')->label('PO ID'),
            Tables\Columns\TextColumn::make('purchaseOrder.Item_Name')->label('Item Name'),
            Tables\Columns\TextColumn::make('purchaseOrder.Item_Quantity')->label('Qty'),
            Tables\Columns\TextColumn::make('purchaseOrder.Item_Price')->label('Price')->money('IDR', true),
            Tables\Columns\TextColumn::make('purchaseOrder.Total_Amount')->label('PO Total')->money('IDR', true),
            Tables\Columns\TextColumn::make('purchaseOrder.supplier.Name')->label('Supplier'),
            Tables\Columns\TextColumn::make('purchaseOrder.employee.Name')->label('Created By'),
            Tables\Columns\TextColumn::make('Invoice_Amount')->label('Invoice Amount')->money('IDR', true),
            Tables\Columns\TextColumn::make('Due_Date')->date()->label('Due Date'),
            Tables\Columns\TextColumn::make('Payment_Status')->label('Status'),
            Tables\Columns\TextColumn::make('Invoice_Date')->date()->label('Invoice Date'),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('Payment_Status')
                ->options([
                    'Paid' => 'Paid',
                    'Pending' => 'Pending',
                ]),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'purchaseOrder',
            'purchaseOrder.supplier',
            'purchaseOrder.employee',
        ]);
    }

    /**
     * Generate automatic invoice number
     * Format: INV-YYYYMM-XXXX (e.g., INV-202505-0001)
     */
    private static function generateInvoiceNumber(): string
    {
        $currentMonth = now()->format('Ym'); // 202505
        $prefix = 'INV-' . $currentMonth . '-';
        
        // Get the last invoice number for current month
        $lastInvoice = Invoice::where('Invoice_Number', 'like', $prefix . '%')
            ->orderBy('Invoice_Number', 'desc')
            ->first();
        
        if ($lastInvoice) {
            // Extract the sequential number and increment it
            $lastNumber = (int) substr($lastInvoice->Invoice_Number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            // First invoice of the month
            $nextNumber = 1;
        }
        
        // Format with leading zeros (4 digits)
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}