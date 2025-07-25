<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;


class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Finance Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('Invoice_ID')
                ->label('Invoice')
                ->relationship('invoice', 'Invoice_Number')
                ->searchable()
                ->preload()
                ->reactive()
                ->required(),

            Forms\Components\Card::make([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Placeholder::make('PO_Number')
                        ->label('PO Number')
                        ->content(fn ($get) => optional(optional(Invoice::with('purchaseOrder')->find($get('Invoice_ID')))?->purchaseOrder)?->PO_ID ?? '-'),

                    Forms\Components\Placeholder::make('Item_Name')
                        ->label('Item Name')
                        ->content(fn ($get) => optional(optional(Invoice::with('purchaseOrder')->find($get('Invoice_ID')))?->purchaseOrder)?->Item_Name ?? '-'),

                    Forms\Components\Placeholder::make('Invoice_Amount_display')
                        ->label('Invoice Amount')
                        ->content(fn ($get) => number_format(optional(Invoice::find($get('Invoice_ID')))?->Invoice_Amount ?? 0, 0, ',', '.') . ' IDR'),

                    Forms\Components\Placeholder::make('Total_Paid')
                        ->label('Total Paid')
                        ->content(function ($get) {
                            $totalPaid = DB::table('payments')
                                ->where('Invoice_ID', $get('Invoice_ID'))
                                ->sum('Amount_Paid');
                            return number_format($totalPaid, 0, ',', '.') . ' IDR';
                        }),

                    Forms\Components\Placeholder::make('Outstanding')
                        ->label('Outstanding')
                        ->content(function ($get) {
                            $invoice = Invoice::find($get('Invoice_ID'));
                            $totalPaid = DB::table('payments')
                                ->where('Invoice_ID', $get('Invoice_ID'))
                                ->sum('Amount_Paid');
                            return number_format(($invoice?->Invoice_Amount ?? 0) - $totalPaid, 0, ',', '.') . ' IDR';
                        }),

                    Forms\Components\Placeholder::make('Due_Date')
                        ->label('Due Date')
                        ->content(fn ($get) => optional(Invoice::find($get('Invoice_ID')))?->Due_Date?->format('d M Y') ?? '-'),

                    Forms\Components\Placeholder::make('Payment_Status')
                        ->label('Payment Status')
                        ->content(fn ($get) => optional(Invoice::find($get('Invoice_ID')))?->Payment_Status ?? '-'),
                ])
            ])->visible(fn ($get) => $get('Invoice_ID') !== null),

            Forms\Components\Card::make([
                Forms\Components\TextInput::make('Amount_Paid')
                    ->label('Amount Paid')
                    ->numeric()
                    ->required(),

                Forms\Components\DatePicker::make('Payment_Date')
                    ->label('Payment Date')
                    ->required(),

                Forms\Components\Select::make('Payment_Method')
                    ->label('Payment Method')
                    ->options([
                        'Bank Transfer' => 'Bank Transfer',
                        'Check' => 'Check',
                        'Cash' => 'Cash',
                    ])
                    ->required(),
            ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice.Invoice_Number')->label('Invoice #')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('invoice.Invoice_Amount')->label('Invoice Amount')->money('IDR', true),
                Tables\Columns\TextColumn::make('Amount_Paid')->label('Amount Paid')->money('IDR', true),
                Tables\Columns\TextColumn::make('Payment_Date')->date()->label('Payment Date'),
                Tables\Columns\TextColumn::make('Payment_Method')->label('Payment Method'),
                Tables\Columns\TextColumn::make('invoice.Due_Date')->label('Due Date')->date(),
                Tables\Columns\TextColumn::make('invoice.Payment_Status')->label('Payment Status'),
                Tables\Columns\TextColumn::make('Outstanding')
                ->label('Outstanding')
                ->state(function ($record) {
                    $invoiceAmount = $record->invoice?->Invoice_Amount ?? 0;
                    $totalPaid = DB::table('payments')
                        ->where('Invoice_ID', $record->Invoice_ID)
                        ->sum('Amount_Paid');

                    return number_format($invoiceAmount - $totalPaid, 0, ',', '.') . ' IDR';
                }),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('Payment_Method')
                    ->options([
                        'Bank Transfer' => 'Bank Transfer',
                        'Check' => 'Check',
                        'Cash' => 'Cash',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // PDF Generation Action
                Action::make('generatePdf')
                    ->label('Generate PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (Payment $record) {
                        return static::generatePaymentPdf($record);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    // Bulk PDF Generation Action
                    BulkAction::make('generateBulkPdf')
                        ->label('Generate PDF Report')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function (Collection $records) {
                            return static::generateBulkPaymentPdf($records);
                        }),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['invoice', 'invoice.purchaseOrder']);
    }

    /**
     * Generate PDF for single payment record
     */
    public static function generatePaymentPdf(Payment $payment)
    {
        // Calculate outstanding amount
        $totalPaid = DB::table('payments')
            ->where('Invoice_ID', $payment->Invoice_ID)
            ->sum('Amount_Paid');
        
        $outstanding = ($payment->invoice?->Invoice_Amount ?? 0) - $totalPaid;

        $data = [
            'payment' => $payment,
            'outstanding' => $outstanding,
            'generated_at' => now()->format('d M Y H:i:s'),
        ];

        $pdf = Pdf::loadView('pdf.payment-receipt', $data);
        
        $filename = 'payment-receipt-' . $payment->invoice?->Invoice_Number . '-' . now()->format('Ymd-His') . '.pdf';
        
        return Response::streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Generate bulk PDF report for multiple payments
     */
    public static function generateBulkPaymentPdf(Collection $payments)
    {
        // Prepare data with outstanding calculations
        $paymentsData = $payments->map(function ($payment) {
            $totalPaid = DB::table('payments')
                ->where('Invoice_ID', $payment->Invoice_ID)
                ->sum('Amount_Paid');
            
            $outstanding = ($payment->invoice?->Invoice_Amount ?? 0) - $totalPaid;
            
            return [
                'payment' => $payment,
                'outstanding' => $outstanding,
            ];
        });

        // Calculate summary
        $summary = [
            'total_records' => $payments->count(),
            'total_amount_paid' => $payments->sum('Amount_Paid'),
            'total_invoice_amount' => $payments->sum(fn($p) => $p->invoice?->Invoice_Amount ?? 0),
            'total_outstanding' => $paymentsData->sum('outstanding'),
        ];

        $data = [
            'payments' => $paymentsData,
            'summary' => $summary,
            'generated_at' => now()->format('d M Y H:i:s'),
        ];

        $pdf = Pdf::loadView('pdf.payments-report', $data);
        
        $filename = 'payments-report-' . now()->format('Ymd-His') . '.pdf';
        
        return Response::streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}