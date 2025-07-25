<?php
namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationLabel = 'Suppliers';
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Supplier Management';
    
    // Form for creating and editing suppliers
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('Contact_Email')
                    ->required()
                    ->email(),
                TextInput::make('Phone_Number')
                    ->required()
                    ->maxLength(50),
                Textarea::make('Address')
                    ->required(),
                
                Select::make('Payment_Terms')
                    ->label('Payment Terms')
                    ->required()
                    ->options([
                        'net_30' => 'Net 30',
                        'net_60' => 'Net 60',
                        'net_90' => 'Net 90',
                        'cash_on_delivery' => 'Cash on Delivery',
                        'prepayment' => 'Prepayment',
                    ])
                    ->searchable(),

                Select::make('Supplier_Type')
                ->label('Supplier Type')
                ->required()
                ->options([
                    'manufacturer' => 'Manufacturer',
                    'distributor' => 'Distributor',
                    'wholesaler' => 'Wholesaler',
                    'retailer' => 'Retailer',
                    'service_provider' => 'Service Provider',
                ])
                ->searchable(),

                TextInput::make('Country')
                    ->required()
                    ->maxLength(100),

                TextInput::make('State')
                    ->required()
                    ->maxLength(100),

                Select::make('Product_Service_Type')
                    ->label('Product/Service Type')
                    ->required()
                    ->options([
                        'product' => 'Product',
                        'service' => 'Service',
                    ])
                    ->searchable(),
            ]);
    }

    // Table for listing suppliers
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('Name')->sortable()->searchable(),
                TextColumn::make('Contact_Email')->sortable()->searchable(),
                TextColumn::make('Phone_Number')->sortable(),
                TextColumn::make('Address')->sortable()->limit(50),
                TextColumn::make('Payment_Terms')->sortable()->limit(50),
                TextColumn::make('Supplier_Type')->sortable()->limit(50),
                TextColumn::make('Country')->sortable()->limit(50),
                TextColumn::make('State')->sortable()->limit(50),
                TextColumn::make('Product_Service_Type')->sortable()->limit(50),
            ])
            ->filters([ 
                // You can add filters here if needed
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    // Relations (if any) for this resource
    public static function getRelations(): array
    {
        return [
            // You can add relation managers here if needed
        ];
    }

    // Pages for this resource (index, create, edit)
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}