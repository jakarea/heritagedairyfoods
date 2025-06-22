<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\{TextInput, Textarea, Select, DateTimePicker, Section};
use Filament\Forms\Get; 
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static ?string $navigationGroup = 'Customer Management';
    protected static ?string $navigationBadgeTooltip = 'The number of orders';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Customer Info')
                ->schema([
                    TextInput::make('name')
                        ->label('Full Name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->required()
                        ->unique(table: 'customers', column: 'email', ignoreRecord: true),

                    TextInput::make('phone')
                        ->label('Phone Number')
                        ->required()
                        ->tel()
                        ->maxLength(20),

                    TextInput::make('street_address')
                        ->label('Street Address')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('district')
                        ->label('District')
                        ->required(),

                    TextInput::make('upazila')
                        ->label('Upazila / Thana')
                        ->required(),

                    TextInput::make('zip_code')
                        ->label('Zip Code')
                        ->numeric()
                        ->maxLength(10),

                    TextInput::make('city')
                        ->label('City')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('country')
                        ->label('Country')
                        ->default('Bangladesh')
                        ->required()
                        ->maxLength(100),

                    DateTimePicker::make('verified_at')
                        ->label('Verified At')
                        ->nullable(),
                    TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->nullable(),
                    TextInput::make('notes')
                        ->label('Notes')
                        ->maxLength(500)
                        ->nullable(),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->sortable(),

                TextColumn::make('district')
                    ->sortable(),

                TextColumn::make('upazila')
                    ->label('Thana')
                    ->sortable(),

                TextColumn::make('city')
                    ->sortable(),

                TextColumn::make('country')
                    ->sortable(),

                TextColumn::make('verified_at')
                    ->label('Verified')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->since(), // shows “2 days ago”

            ])
            ->filters([
                // Example filter (optional)
                Tables\Filters\SelectFilter::make('district')
                    ->options(\App\Models\Customer::query()->pluck('district', 'district')->unique())
                    ->label('Filter by District'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
