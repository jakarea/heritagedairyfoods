<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{TextInput, Textarea, Select, DateTimePicker, Section};
use Filament\Tables\Columns\TextColumn; 
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Illuminate\Support\Carbon;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\{EditAction, DeleteAction, ViewAction, ForceDeleteAction, RestoreAction};

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

                    TextInput::make('country')
                        ->label('Country')
                        ->default('Bangladesh')
                        ->required()
                        ->maxLength(100),



                    Select::make('division_id')
                        ->label('Division')
                        ->options(Division::all()->pluck('name', 'id'))
                        ->reactive()
                        ->afterStateUpdated(fn(callable $set) => $set('district_id', null)) // Reset district when division changes
                        ->required(),

                    Select::make('district_id')
                        ->label('District')
                        ->options(function (callable $get) {
                            $divisionId = $get('division_id');
                            if ($divisionId) {
                                return District::where('division_id', $divisionId)->pluck('name', 'id');
                            }
                            return [];
                        })
                        ->reactive()
                        ->afterStateUpdated(fn(callable $set) => $set('thana_id', null)) // Reset thana when district changes
                        ->required(),

                    Select::make('thana_id')
                        ->label('Upazila / Thana')
                        ->options(function (callable $get) {
                            $districtId = $get('district_id');
                            if ($districtId) {
                                return Thana::where('district_id', $districtId)->pluck('name', 'id');
                            }
                            return [];
                        })
                        ->required(),

                    TextInput::make('street_address')
                        ->label('Street Address')
                        ->required()
                        ->maxLength(255),

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

                TextColumn::make('country')
                    ->sortable(),

                TextColumn::make('division.name')
                    ->sortable(),

                TextColumn::make('district.name')
                    ->sortable(),

                TextColumn::make('thana.name')
                    ->sortable(), 

                TextColumn::make('street_address')->limit(35)->searchable(), 

            ])
            ->filters([
                // Example filter (optional)
                Tables\Filters\SelectFilter::make('district_id')
                    ->options(\App\Models\Customer::query()->pluck('district_id', 'district_id')->unique())
                    ->label('Filter by District'),
            ])
            ->actions([
                ActionGroup::make([
                    // Action::make('view-order-details')
                    //     ->label('Order Details')
                    //     ->icon('heroicon-o-currency-dollar')
                    //     ->color('info')
                    //     ->url(fn($record) => url('admin/orders/details', ['id' => $record->id])),
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
        ->schema([
            InfolistSection::make('Basic Information')
                ->columns(2)
                ->schema([
                    TextEntry::make('name')
                        ->label('Customer Name')
                        ->weight('bold'),
                    TextEntry::make('email')
                        ->label('Email Address'),
                    TextEntry::make('phone')
                        ->label('Phone Number'),
                    TextEntry::make('country')
                        ->label('Country')
                        ->placeholder('Not specified'),
                ]),

            InfolistSection::make('Location Details')
                ->columns(3)
                ->schema([
                    TextEntry::make('division.name')
                        ->label('Division')
                        ->placeholder('Not specified')
                        ->default('Not specified'),
                    TextEntry::make('district.name')
                        ->label('District')
                        ->placeholder('Not specified')
                        ->default('Not specified'),
                    TextEntry::make('thana.name')
                        ->label('Thana / Upazila')
                        ->placeholder('Not specified')
                        ->default('Not specified'),
                ]),

            InfolistSection::make('Address')
                ->schema([
                    TextEntry::make('street_address')
                        ->label('Street Address')
                        ->placeholder('Not specified')
                        ->columnSpanFull(),
                ]),

            InfolistSection::make('Additional Information')
                ->columns(12)
                ->schema([
                    TextEntry::make('verified_at')
                        ->label('Verified At')
                        ->columnSpan(2)
                        ->placeholder('Not verified')
                        ->dateTime('Y-m-d H:i:s')
                        ->formatStateUsing(fn ($state): string => $state ? Carbon::parse($state)->diffForHumans() : 'Not verified'),
                    TextEntry::make('notes')
                        ->label('Notes')
                        ->placeholder('No notes available')
                        ->columnSpan(10)
                        ->markdown(),
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
