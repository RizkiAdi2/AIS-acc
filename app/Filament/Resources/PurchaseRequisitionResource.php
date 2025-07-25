<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseRequisitionResource\Pages;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;

class PurchaseRequisitionResource extends Resource
{
    protected static ?string $model = PurchaseRequisition::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Purchase Requisitions';
    protected static ?string $modelLabel = 'Purchase Requisition';
    protected static ?string $pluralModelLabel = 'Purchase Requisitions';
    protected static ?string $navigationGroup = 'Purchasing';
    // Method for PDF generation
    public static function generatePDF($record = null)
    {
        try {
            // Get data based on whether we're generating for a single record or all
            if ($record) {
                // For a single record
                $data = collect([$record]);
                $filename = 'purchase_requisition_' . $record->id . '.pdf';
            } else {
                // For all records
                $data = PurchaseRequisition::with('employee')->latest()->get();
                $filename = 'purchase_requisitions_report_' . Carbon::now()->format('Y-m-d_H-i-s') . '.pdf';
            }

            // Generate PDF
            $pdf = PDF::loadView('pdf.purchase_requisitions', compact('data'));
            
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

   // Replace the form method with this updated version
public static function form(Form $form): Form
{
    return $form
        ->schema([
           Forms\Components\Select::make('Employee_ID')
                ->options(function() {
                    return Employee::query()
                        ->select(['Employee_ID', 'Name'])
                        ->get()
                        ->pluck('Name', 'Employee_ID')
                        ->toArray();
                })
                ->label('Employee')
                ->required(),

             Select::make('Department')
            ->required()
            ->options([
                'HR' => 'Human Resources',
                'IT' => 'Information Technology',
                'Finance' => 'Finance',
                'Marketing' => 'Marketing',
                'Sales' => 'Sales',
                'Operations' => 'Operations',
                'Production' => 'Production',
                'Inventory' => 'Inventory',
                'Purchasing' => 'Purchasing',
                'Logistics' => 'Logistics',
                'Engineering' => 'Engineering',
            ])
            ->searchable(),

            Forms\Components\DatePicker::make('Date_Requested')
                ->default(now())
                ->required(),

            Forms\Components\Textarea::make('Description')
                ->nullable()
                ->maxLength(500),

            Forms\Components\TextInput::make('Item_Name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('Item_Quantity')
                ->required()
                ->numeric()
                ->minValue(1)
                ->reactive()
                ->afterStateUpdated(function ($set, $get) {
                    // Calculate total cost when quantity changes
                    $quantity = $get('Item_Quantity') ?? 0;
                    $price = $get('Item_Price') ?? 0;
                    $set('Total_Cost', $quantity * $price);
                }),

            Forms\Components\TextInput::make('Item_Price')
                ->required()
                ->numeric()
                ->minValue(0)
                ->reactive()
                ->afterStateUpdated(function ($set, $get) {
                    // Calculate total cost when price changes
                    $quantity = $get('Item_Quantity') ?? 0;
                    $price = $get('Item_Price') ?? 0;
                    $set('Total_Cost', $quantity * $price);
                }),

            Forms\Components\DatePicker::make('Expected_Delivery_Date')
                ->required()
                ->after('Date_Requested'),

            Forms\Components\TextInput::make('Total_Cost')
                ->label('Total Cost')
                ->disabled()
                ->numeric()
                ->dehydrated(), // Include in form submission even if disabled

            Forms\Components\Select::make('Status')
                ->options([
                    'Pending' => 'Pending',
                    'Approved' => 'Approved',
                    'Rejected' => 'Rejected',
                ])
                ->default('Pending')
                ->required()
                ->reactive(),


              // Add checkbox for approval that shows only when status is pending
            Checkbox::make('approve_and_create_po')
                ->label('Approve and Create Purchase Order')
                ->visible(fn (callable $get) => $get('Status') === 'Pending')
                ->helperText('Check this to approve the requisition and automatically create a Purchase Order'),
            
            // Add supplier selection that appears when the checkbox is checked
            Select::make('Supplier_ID')
                ->label('Select Supplier for PO')
                ->options(function() {
                    return Supplier::query()
                        ->select(['Supplier_ID', 'Name'])
                        ->get()
                        ->pluck('Name', 'Supplier_ID')
                        ->toArray();
                })
                ->visible(fn (callable $get) => $get('approve_and_create_po') === true)
                ->required(fn (callable $get) => $get('approve_and_create_po') === true)
                ->helperText('Required when creating a Purchase Order'),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.Name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('Department')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('Item_Name')
                    ->label('Item Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('Item_Quantity')
                    ->label('Quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('Item_Price')
                    ->label('Price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('Total_Cost')
                    ->label('Total Cost')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('Expected_Delivery_Date')
                    ->date()
                    ->label('Expected Delivery')
                    ->sortable(),
                Tables\Columns\TextColumn::make('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        'Pending' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('Date_Requested')
                    ->date()
                    ->sortable(),
                    // Add column to show if PO has been created
                Tables\Columns\IconColumn::make('has_purchase_order')
                    ->label('PO Created')
                    ->boolean()
                    ->getStateUsing(function (PurchaseRequisition $record) {
                        return $record->purchaseOrders()->exists();
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ]),
                Tables\Filters\Filter::make('Department')
                    ->form([
                        Forms\Components\TextInput::make('search')
                            ->placeholder('Search by department')
                    ])
                    ->query(fn ($query, array $data) =>
                        $query->when(
                            $data['search'] ?? null,
                            fn ($query, $search) => $query->where('Department', 'like', '%' . $search . '%')
                        )
                    ),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn ($query, $date) => $query->whereDate('Date_Requested', '>=', $date)
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn ($query, $date) => $query->whereDate('Date_Requested', '<=', $date)
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                // Add an action for individual PDF download
                Tables\Actions\Action::make('Download PDF')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(fn (PurchaseRequisition $record) => static::generatePDF($record)),
                    // Add action to approve and create PO directly from table
                Tables\Actions\Action::make('approve_and_create_po')
                    ->label('Approve & Create PO')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->form([
                        Select::make('Supplier_ID')
                            ->label('Select Supplier')
                            ->options(function() {
                                return Supplier::query()
                                    ->select(['Supplier_ID', 'Name'])
                                    ->get()
                                    ->pluck('Name', 'Supplier_ID')
                                    ->toArray();
                            })
                            ->required(),
                    ])
                    ->visible(fn (PurchaseRequisition $record) => 
                        $record->Status === 'Pending' && !$record->purchaseOrders()->exists()
                    )
                    ->action(function (PurchaseRequisition $record, array $data) {
                        // Update status to Approved
                        $record->Status = 'Approved';
                        $record->save();
                        
                        // Create a new Purchase Order
                        $purchaseOrder = new PurchaseOrder();
                        $purchaseOrder->Requisition_ID = $record->Requisition_ID;
                        $purchaseOrder->Supplier_ID = $data['Supplier_ID'];
                        $purchaseOrder->Employee_ID = $record->Employee_ID;
                        $purchaseOrder->Order_Date = now();
                        $purchaseOrder->Expected_Delivery_Date = $record->Expected_Delivery_Date;
                        $purchaseOrder->Item_Name = $record->Item_Name;
                        $purchaseOrder->Item_Description = $record->Description;
                        $purchaseOrder->Item_Quantity = $record->Item_Quantity;
                        $purchaseOrder->Item_Price = $record->Item_Price;
                        $purchaseOrder->Total_Amount = $record->Total_Cost;
                        $purchaseOrder->Status = 'Pending';
                        $purchaseOrder->Notes = 'Created from Purchase Requisition #' . $record->Requisition_ID;
                        $purchaseOrder->save();
                        
                        Notification::make()
                            ->title('Purchase Order Created')
                            ->body('Purchase Order #' . $purchaseOrder->PO_ID . ' has been created successfully.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    // Add bulk action for status change
                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-o-check-circle')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'Pending' => 'Pending',
                                    'Approved' => 'Approved',
                                    'Rejected' => 'Rejected',
                                ])
                                ->required(),
                                Checkbox::make('create_po')
                                ->label('Create Purchase Orders for Approved Requisitions')
                                ->visible(fn (callable $get) => $get('status') === 'Approved')
                                ->helperText('This will create POs for all selected requisitions that don\'t already have one'),
                            Select::make('Supplier_ID')
                                ->label('Select Supplier for POs')
                                ->options(function() {
                                    return Supplier::query()
                                        ->select(['Supplier_ID', 'Name'])
                                        ->get()
                                        ->pluck('Name', 'Supplier_ID')
                                        ->toArray();
                                })
                                ->visible(fn (callable $get) => $get('create_po') === true && $get('status') === 'Approved')
                                ->required(fn (callable $get) => $get('create_po') === true && $get('status') === 'Approved'),
                        ])
                        ->action(function ($records, array $data) {
                            $poCreatedCount = 0;
                            
                            $records->each(function ($record) use ($data, &$poCreatedCount) {
                                $record->update(['Status' => $data['status']]);
                            
                                // If approved and create_po is checked, create PO for records without one
                                if ($data['status'] === 'Approved' && 
                                    isset($data['create_po']) && 
                                    $data['create_po'] && 
                                    !$record->purchaseOrders()->exists()) {
                                    
                                    // Create a new Purchase Order
                                    $purchaseOrder = new PurchaseOrder();
                                    $purchaseOrder->Requisition_ID = $record->Requisition_ID;
                                    $purchaseOrder->Supplier_ID = $data['Supplier_ID'];
                                    $purchaseOrder->Employee_ID = $record->Employee_ID;
                                    $purchaseOrder->Order_Date = now();
                                    $purchaseOrder->Expected_Delivery_Date = $record->Expected_Delivery_Date;
                                    $purchaseOrder->Item_Name = $record->Item_Name;
                                    $purchaseOrder->Item_Description = $record->Description;
                                    $purchaseOrder->Item_Quantity = $record->Item_Quantity;
                                    $purchaseOrder->Item_Price = $record->Item_Price;
                                    $purchaseOrder->Total_Amount = $record->Total_Cost;
                                    $purchaseOrder->Status = 'Pending';
                                    $purchaseOrder->Notes = 'Bulk created from Purchase Requisition #' . $record->Requisition_ID;
                                    $purchaseOrder->save();
                                    
                                    $poCreatedCount++;
                                }
                            });
                            
                            // Show notification about the results
                            if ($poCreatedCount > 0) {
                                Notification::make()
                                    ->title('Purchase Orders Created')
                                    ->body("Successfully created {$poCreatedCount} purchase orders.")
                                    ->success()
                                    ->send();
                            }
                            
                            Notification::make()
                                ->title('Status Updated')
                                ->body('Selected requisitions have been updated to ' . $data['status'])
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            // ->headerActions([
            //     // Add action for all records PDF download
            //     Tables\Actions\Action::make('downloadAllPDF')
            //         ->label('Download All as PDF')
            //         ->icon('heroicon-o-document')
            //         ->color('primary')
            //         ->action(fn () => static::generatePDF()),
            // ])
            ;      
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
            'index' => Pages\ListPurchaseRequisitions::route('/'),
            'create' => Pages\CreatePurchaseRequisition::route('/create'),
            'edit' => Pages\EditPurchaseRequisition::route('/{record}/edit'),
        ];
    }
}