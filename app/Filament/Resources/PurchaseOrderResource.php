<?php
namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Supplier;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Components\Toggle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    
    protected static ?string $navigationGroup = 'Purchasing';

    protected static ?int $navigationSort = 2;

    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('is_direct_po')
                    ->label('Create Direct Purchase Order')
                    ->default(false)
                    ->live()
                    ->disabled(fn ($record) => $record && $record->Status === 'Completed'),
                    
                Forms\Components\Select::make('Requisition_ID')
                    ->label('Purchase Requisition')
                    ->options(function() {
                        return PurchaseRequisition::query()
                            ->select(['Requisition_ID'])
                            ->get()
                            ->pluck('Requisition_ID', 'Requisition_ID')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->hidden(fn (Forms\Get $get) => $get('is_direct_po'))
                    ->required(fn (Forms\Get $get) => !$get('is_direct_po'))
                    ->live() 
                    ->disabled(fn ($record) => $record && $record->Status === 'Completed')
                    ->afterStateUpdated(function ($state, Forms\Set $set, $livewire) {
                        if (!$state) {
                            // Clear fields if requisition is deselected
                            self::clearFormFields($set);
                            return;
                        }
                        
                        try {
                            // Fetch the requisition data with a more direct approach
                            $requisition = PurchaseRequisition::findOrFail($state);
                            
                            // Debug the requisition data - this will help for troubleshooting
                            Log::info("Auto-filling PO form from Requisition #{$state}:", [
                                'Supplier_ID' => $requisition->Supplier_ID ?? 'N/A',
                                'Employee_ID' => $requisition->Employee_ID ?? 'N/A',
                                'Item_Name' => $requisition->Item_Name ?? 'N/A',
                                'Description' => $requisition->Description ?? 'N/A', 
                                'Total_Cost' => $requisition->Total_Cost ?? 'N/A',
                                'Expected_Delivery_Date' => $requisition->Expected_Delivery_Date,
                                'Expected_Delivery_Date_Type' => gettype($requisition->Expected_Delivery_Date)
                            ]);
                            
                            // Auto-fill form fields with direct assignments
                            $set('Supplier_ID', $requisition->Supplier_ID);
                            $set('Employee_ID', $requisition->Employee_ID);
                            
                            // Menangani Expected_Delivery_Date dengan lebih baik
                            if ($requisition->Expected_Delivery_Date) {
                                if ($requisition->Expected_Delivery_Date instanceof \DateTime) {
                                    $set('Expected_Delivery_Date', $requisition->Expected_Delivery_Date->format('Y-m-d'));
                                } else {
                                    // Coba konversi string menjadi format tanggal yang benar
                                    try {
                                        $date = Carbon::parse($requisition->Expected_Delivery_Date);
                                        $set('Expected_Delivery_Date', $date->format('Y-m-d'));
                                    } catch (\Exception $e) {
                                        Log::warning("Failed to parse Expected_Delivery_Date: " . $e->getMessage());
                                        $set('Expected_Delivery_Date', null);
                                    }
                                }
                            } else {
                                $set('Expected_Delivery_Date', null);
                            }
                            
                            $set('Item_Name', $requisition->Item_Name);
                            $set('Item_Description', $requisition->Description); // Map Description -> Item_Description
                            $set('Item_Quantity', $requisition->Item_Quantity);
                            $set('Item_Price', $requisition->Item_Price);
                            $set('Total_Amount', $requisition->Total_Cost); // Map Total_Cost -> Total_Amount
                            
                            // Set notes with requisition info
                            $notes = "Automatically created from Purchase Requisition #{$requisition->Requisition_ID}";
                            if (!empty($requisition->Department)) {
                                $notes .= "\nDepartment: {$requisition->Department}";
                            }
                            $set('Notes', $notes);
                            
                            // Show notification on success
                            \Filament\Notifications\Notification::make()
                                ->title('Data Loaded')
                                ->body('Data from Purchase Requisition has been loaded successfully.')
                                ->success()
                                ->send();
                                
                        } catch (\Exception $e) {
                            // Log error for debugging
                            Log::error("Error auto-filling PO form from Requisition #{$state}: " . $e->getMessage());
                            
                            // Show error notification
                            \Filament\Notifications\Notification::make()
                                ->title('Error Loading Data')
                                ->body('Failed to load data from Purchase Requisition. Please try again.')
                                ->danger()
                                ->send();
                                
                            // Clear form to avoid partial data
                            self::clearFormFields($set);
                        }
                    }),
                
                // Button to manually reload data if needed
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('reload_requisition_data')
                        ->label('Reload Requisition Data')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->action(function (Forms\Get $get, Forms\Set $set) {
                            $requisitionId = $get('Requisition_ID');
                            if (!$requisitionId) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Error')
                                    ->body('No Purchase Requisition selected')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            try {
                                // Reload requisition data
                                $requisition = PurchaseRequisition::findOrFail($requisitionId);
                                
                                // Log tanggal untuk debugging
                                Log::info("Reloading data, Expected_Delivery_Date value:", [
                                    'raw_value' => $requisition->Expected_Delivery_Date,
                                    'type' => gettype($requisition->Expected_Delivery_Date)
                                ]);
                                
                                // Auto-fill form fields
                                $set('Supplier_ID', $requisition->Supplier_ID);
                                $set('Employee_ID', $requisition->Employee_ID);
                                
                                // Menangani Expected_Delivery_Date dengan lebih baik
                                if ($requisition->Expected_Delivery_Date) {
                                    if ($requisition->Expected_Delivery_Date instanceof \DateTime) {
                                        $set('Expected_Delivery_Date', $requisition->Expected_Delivery_Date->format('Y-m-d'));
                                    } else {
                                        // Coba konversi string menjadi format tanggal yang benar
                                        try {
                                            $date = Carbon::parse($requisition->Expected_Delivery_Date);
                                            $set('Expected_Delivery_Date', $date->format('Y-m-d'));
                                        } catch (\Exception $e) {
                                            Log::warning("Failed to parse Expected_Delivery_Date during reload: " . $e->getMessage());
                                            $set('Expected_Delivery_Date', null);
                                        }
                                    }
                                } else {
                                    $set('Expected_Delivery_Date', null);
                                }
                                
                                $set('Item_Name', $requisition->Item_Name);
                                $set('Item_Description', $requisition->Description);
                                $set('Item_Quantity', $requisition->Item_Quantity);
                                $set('Item_Price', $requisition->Item_Price);
                                $set('Total_Amount', $requisition->Total_Cost);
                                
                                // Set notes with requisition info
                                $notes = "Automatically created from Purchase Requisition #{$requisition->Requisition_ID}";
                                if (!empty($requisition->Department)) {
                                    $notes .= "\nDepartment: {$requisition->Department}";
                                }
                                $set('Notes', $notes);
                                
                                \Filament\Notifications\Notification::make()
                                    ->title('Success')
                                    ->body('Requisition data reloaded successfully')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Log::error("Reload data error: " . $e->getMessage());
                                \Filament\Notifications\Notification::make()
                                    ->title('Error')
                                    ->body('Failed to reload requisition data: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn (Forms\Get $get) => !$get('is_direct_po') && $get('Requisition_ID')),
                ])
                ->visible(fn (Forms\Get $get) => !$get('is_direct_po') && $get('Requisition_ID')),
                        
                Forms\Components\Select::make('Supplier_ID')
                    ->options(function() {
                        return Supplier::query()
                            ->select(['Supplier_ID', 'Name'])
                            ->get()
                            ->pluck('Name', 'Supplier_ID')
                            ->toArray();
                    })
                    ->label('Supplier')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn (Forms\Get $get, $record) => (!$get('is_direct_po') && $get('Requisition_ID')) || ($record && $record->Status === 'Completed')),
    
                Forms\Components\Select::make('Employee_ID')
                    ->options(function() {
                        return Employee::query()
                            ->select(['Employee_ID', 'Name'])
                            ->get()
                            ->pluck('Name', 'Employee_ID')
                            ->toArray();
                    })
                    ->label('Requested By')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn (Forms\Get $get, $record) => (!$get('is_direct_po') && $get('Requisition_ID')) || ($record && $record->Status === 'Completed')),
    
                Forms\Components\DatePicker::make('Order_Date')
                    ->label('Order Date')
                    ->default(now())
                    ->format('Y-m-d') // Format yang diharapkan oleh Filament
                    ->required()
                    ->disabled(fn ($record) => $record && $record->Status === 'Completed'),
    
                Forms\Components\DatePicker::make('Expected_Delivery_Date')
                    ->label('Expected Delivery Date')
                    ->format('Y-m-d') // Format yang diharapkan oleh Filament
                    ->required()
                    ->disabled(fn (Forms\Get $get, $record) => (!$get('is_direct_po') && $get('Requisition_ID')) || ($record && $record->Status === 'Completed')),
    
                Forms\Components\TextInput::make('Item_Name')
                    ->label('Item Name')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn (Forms\Get $get, $record) => (!$get('is_direct_po') && $get('Requisition_ID')) || ($record && $record->Status === 'Completed')),
    
                Forms\Components\Textarea::make('Item_Description')
                    ->label('Item Description')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->disabled(fn (Forms\Get $get, $record) => (!$get('is_direct_po') && $get('Requisition_ID')) || ($record && $record->Status === 'Completed')),
    
                Forms\Components\TextInput::make('Item_Quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->required()
                    ->live()
                    ->disabled(fn (Forms\Get $get, $record) => (!$get('is_direct_po') && $get('Requisition_ID')) || ($record && $record->Status === 'Completed'))
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $price = $get('Item_Price') ?? 0;
                        $set('Total_Amount', $state * $price);
                    }),
    
                Forms\Components\TextInput::make('Item_Price')
                    ->label('Price per Unit')
                    ->numeric()
                    ->required()
                    ->live()
                    ->disabled(fn (Forms\Get $get, $record) => (!$get('is_direct_po') && $get('Requisition_ID')) || ($record && $record->Status === 'Completed'))
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $quantity = $get('Item_Quantity') ?? 0;
                        $set('Total_Amount', $state * $quantity);
                    }),
    
                Forms\Components\TextInput::make('Total_Amount')
                    ->label('Total Amount')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),
    
                Forms\Components\Select::make('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                        'Completed' => 'Completed',
                    ])
                    ->default('Pending')
                    ->required()
                    ->disabled(fn ($record) => $record && $record->Status === 'Completed'),
    
                Forms\Components\Textarea::make('Notes')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->disabled(fn ($record) => $record && $record->Status === 'Completed'),
            ])
            ->statePath('data');
    }

    // Helper method to clear all form fields
    private static function clearFormFields(Forms\Set $set)
    {
        $set('Supplier_ID', null);
        $set('Employee_ID', null);
        $set('Expected_Delivery_Date', null);
        $set('Item_Name', null);
        $set('Item_Description', null);
        $set('Item_Quantity', null);
        $set('Item_Price', null);
        $set('Total_Amount', null);
        $set('Notes', null);
        
        Log::info('Purchase order form fields cleared');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('PO_ID')
                    ->label('PO #')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchaseRequisition.Requisition_ID')
                    ->label('Requisition #')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Direct PO'),
                Tables\Columns\TextColumn::make('supplier.Name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.Name')
                ->label('Requested By')
                ->searchable()
                ->sortable()
                ->getStateUsing(function (PurchaseOrder $record) {
                    // If this is from a requisition, try to get the employee from there
                    if ($record->purchaseRequisition && $record->purchaseRequisition->employee) {
                        return $record->purchaseRequisition->employee->Name;
                    }
                    // Otherwise use the PO's employee
                    return $record->employee ? $record->employee->Name : 'N/A';
                }),
            // Modified item name column
            Tables\Columns\TextColumn::make('Item_Name')
                ->label('Item')
                ->searchable()
                ->getStateUsing(function (PurchaseOrder $record) {
                    // If this is from a requisition, try to get the item name from there
                    if ($record->purchaseRequisition && $record->purchaseRequisition->Item_Name) {
                        return $record->purchaseRequisition->Item_Name;
                    }
                    // Otherwise use the PO's item name
                    return $record->Item_Name ?? 'N/A';
                }),
            // Modified quantity column
            Tables\Columns\TextColumn::make('Item_Quantity')
                ->label('Qty')
                ->numeric()
                ->sortable()
                ->getStateUsing(function (PurchaseOrder $record) {
                    // If this is from a requisition, try to get the quantity from there
                    if ($record->purchaseRequisition && $record->purchaseRequisition->Item_Quantity) {
                        return $record->purchaseRequisition->Item_Quantity;
                    }
                    // Otherwise use the PO's quantity
                    return $record->Item_Quantity ?? 'N/A';
                }),
                Tables\Columns\TextColumn::make('Total_Amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('Order_Date')
                    ->label('Order Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('Expected_Delivery_Date')
                    ->label('Expected Delivery')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Rejected' => 'danger',
                        'Pending' => 'warning',
                        'Approved' => 'success',
                        'Completed' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                        'Completed' => 'Completed',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (PurchaseOrder $record) => $record->Status !== 'Completed'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (PurchaseOrder $record) => $record->Status !== 'Completed'),
                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(fn (PurchaseOrder $record) => self::generatePDF($record)),
                Tables\Actions\Action::make('view')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->url(fn (PurchaseOrder $record): string => PurchaseOrderResource::getUrl('edit', ['record' => $record]))
                    ->visible(fn (PurchaseOrder $record) => $record->Status === 'Completed'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function (Collection $records): void {
                            $records = $records->filter(fn (PurchaseOrder $record) => $record->Status !== 'Completed');
                            $records->each(fn (PurchaseOrder $record) => $record->delete());
                        }),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->action(function (Collection $records): void {
                            $records = $records->filter(fn (PurchaseOrder $record) => $record->Status !== 'Completed');
                            $records->each(fn (PurchaseOrder $record) => $record->forceDelete());
                        }),
                    Tables\Actions\RestoreBulkAction::make()
                        ->action(function (Collection $records): void {
                            $records = $records->filter(fn (PurchaseOrder $record) => $record->Status !== 'Completed');
                            $records->each(fn (PurchaseOrder $record) => $record->restore());
                        }),
                    Tables\Actions\BulkAction::make('download_bulk_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(fn () => self::generatePDF())
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('PO_ID', 'desc');
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
    
    // Add this method to handle direct PO creation without requisition
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function generatePDF($record = null)
    {
        try {
            // Get data based on whether we're generating for a single record or all
            if ($record) {
                // For a single record
                $data = collect([$record]);
                $filename = 'purchase_order_' . $record->PO_ID . '.pdf';
            } else {
                // For all records
                $data = PurchaseOrder::with(['supplier', 'employee', 'purchaseRequisition'])->latest()->get();
                $filename = 'purchase_orders_report_' . Carbon::now()->format('Y-m-d_H-i-s') . '.pdf';
            }

            // Generate PDF
            $pdf = PDF::loadView('pdf.purchase_orders', compact('data'));
            
            // Set paper size and orientation
            $pdf->setPaper('a4', 'portrait');
            
            // Return the downloadable PDF
            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                [
                    'Content-Type' => 'application/pdf',
                ]
            );
        } catch (\Exception $e) {
            // Log the error
            Log::error('PDF Generation Error: ' . $e->getMessage());
            
            // Return with error
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }
}