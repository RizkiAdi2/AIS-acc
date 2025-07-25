<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Models\Inventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Inventory Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('Material_Name')->required()->maxLength(255),
                TextInput::make('Material_Description')->required()->maxLength(255),

                Select::make('Material_Type')->required()->options([
                    'Raw Material' => 'Raw Material',
                    'Finished Goods' => 'Finished Goods',
                    'Packaging Material' => 'Packaging Material',
                    'Consumables' => 'Consumables',
                ])->searchable(),

                TextInput::make('Quantity_Available')
                    ->numeric()
                    ->required()
                    ->reactive(),

                Select::make('Unit_Of_Measurement')
                    ->required()
                    ->options([
                        'Kg' => 'Kg',
                        'L' => 'L',
                        'm' => 'm',
                        'piece' => 'piece',
                    ]),

                TextInput::make('Unit_Cost')
                    ->numeric()
                    ->required()
                    ->reactive(),

                TextInput::make('Reorder_Level')->numeric()->required(),

                Select::make('Location')->required()->options([
                    'Warehouse A' => 'Warehouse A',
                    'Warehouse B' => 'Warehouse B',
                    'Warehouse C' => 'Warehouse C',
                ]),

                Select::make('Supplier_ID')
                    ->label('Supplier')
                    ->relationship('supplier', 'Name')
                    ->required(),

                // Diskon Type
                Select::make('discount_type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed Amount',
                    ])
                    ->nullable()
                    ->reactive(),

                TextInput::make('discount_value')
                    ->numeric()
                    ->nullable()
                    ->reactive(),

                // Kalkulasi otomatis
                TextInput::make('total_cost_after_discount')
                    ->label('Total Cost After Discount')
                    ->disabled()
                    ->dehydrated() // Simpan ke DB
                    ->reactive()
                    ->afterStateHydrated(function ($set, $get) {
                        $unitCost = $get('Unit_Cost') ?? 0;
                        $qty = $get('Quantity_Available') ?? 0;
                        $type = $get('discount_type');
                        $value = $get('discount_value') ?? 0;

                        $total = $unitCost * $qty;

                        if ($type === 'percentage') {
                            $discountAmount = $total * ($value / 100);
                        } elseif ($type === 'fixed') {
                            $discountAmount = $value;
                        } else {
                            $discountAmount = 0;
                        }

                        $set('total_cost_after_discount', $total - $discountAmount);
                    }),

                Select::make('employee_id')
                    ->relationship('employee', 'Name')
                    ->nullable(),

                Select::make('payment_method')
                    ->options([
                        'Bank Transfer' => 'Bank Transfer',
                        'Cash' => 'Cash',
                        'Dana' => 'Dana',
                        'OVO' => 'OVO',
                        'Gopay' => 'Gopay',
                    ])
                    ->nullable(),

                Select::make('payment_term')
                    ->options([
                        'Cash on Delivery' => 'Cash on Delivery',
                        'Net 30' => 'Net 30',
                        'Net 90' => 'Net 90',
                        'Prepayment' => 'Prepayment',
                    ])
                    ->nullable(),

                DatePicker::make('payment_date')->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Material_Name'),
                Tables\Columns\TextColumn::make('Material_Description'),
                Tables\Columns\TextColumn::make('Material_Type'),
                Tables\Columns\TextColumn::make('Quantity_Available'),
                Tables\Columns\TextColumn::make('Unit_Of_Measurement'),
                Tables\Columns\TextColumn::make('Unit_Cost'),
                Tables\Columns\TextColumn::make('Reorder_Level'),
                Tables\Columns\TextColumn::make('Location'),
                Tables\Columns\TextColumn::make('supplier.Name')->label('Supplier'),
                Tables\Columns\TextColumn::make('discount_type'),
                Tables\Columns\TextColumn::make('discount_value'),
                Tables\Columns\TextColumn::make('total_cost_after_discount'),
                Tables\Columns\TextColumn::make('employee.Name'),
                Tables\Columns\TextColumn::make('payment_method'),
                Tables\Columns\TextColumn::make('payment_term'),
                Tables\Columns\TextColumn::make('payment_date')->date(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
