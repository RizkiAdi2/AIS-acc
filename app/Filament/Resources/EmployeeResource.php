<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Human Resource Management';
    protected static ?string $recordTitleAttribute = 'Name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('Name')->required()->maxLength(255),

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

            Select::make('Role')
                ->required()
                ->options([
                    'Director' => 'Director',
                    'Manager' => 'Manager',
                    'Supervisor' => 'Supervisor',
                    'Staff' => 'Staff',
                    'Intern' => 'Intern',
                ])
                ->searchable(),

            TextInput::make('Phone')->tel()->maxLength(15),

            TextInput::make('Email')->email()->unique(ignoreRecord: true),

            Textarea::make('Address')->rows(3)->maxLength(500),

            DatePicker::make('hire_date')->label('Hire Date')->required(),

            Select::make('Status')
                ->options([
                    'Active' => 'Active',
                    'Inactive' => 'Inactive',
                    'On Leave' => 'On Leave',
                    'Sick'    => 'Sick',
                ])
                ->default('Active'),

            Select::make('gender')
                ->options([
                    'Male' => 'Male',
                    'Female' => 'Female',
                ])
                ->nullable()
                ->label('Gender'),

            TextInput::make('salary')
                ->numeric()
                ->nullable()
                ->label('Salary')
                ->maxLength(15),

            TextInput::make('identification_number')
                ->nullable()
                ->label('Identification Number')
                ->maxLength(20),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('Name')->sortable()->searchable(),
                TextColumn::make('Department')->sortable(),
                TextColumn::make('Role')->sortable(),
                TextColumn::make('Phone')->label('Phone')->limit(20),
                TextColumn::make('Email')->label('Email')->limit(30),
                TextColumn::make('hire_date')
                    ->label('Hire Date')
                    ->date('Y-m-d'), // format tanggal
                BadgeColumn::make('Status')
                    ->colors([
                        'success' => 'Active',
                        'warning' => 'On Leave',
                        'danger' => 'Inactive',
                        'secondary' => 'Sick',
                    ]),
                TextColumn::make('gender')->label('Gender'),
                TextColumn::make('salary')->label('Salary')->money('IDR'),
                TextColumn::make('identification_number')->label('Identification Number'),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
                DeleteAction::make(), // Delete akan menjadi hard delete karena tidak ada SoftDeletes
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
