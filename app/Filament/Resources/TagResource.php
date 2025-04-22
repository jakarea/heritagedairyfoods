<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagResource\Pages;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\{TextInput, Select, Textarea, FileUpload};
use Filament\Tables\Columns\{TextColumn, ImageColumn};
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Infolists;
use Filament\Tables\Actions\ActionGroup;
use Filament\Infolists\Infolist;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;
    protected static ?string $navigationBadgeTooltip = 'The number of tags';
    protected static ?string $navigationGroup = 'Products Management';
    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Tag Details')->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $old, ?string $state) {
                            if (($get('slug') ?? '') !== Str::slug($old)) {
                                return;
                            }
                            $set('slug', Str::slug($state));
                        })->columnSpanFull(),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(Tag::class, 'slug', ignoreRecord: true),
                        Select::make('is_active')
                        ->label('Status')
                        ->options([
                            true => 'Active',
                            false => 'Inactive',
                        ])
                        ->default(true)
                        ->required()
                        ->selectablePlaceholder(false)
                        ->native(false),
                    Textarea::make('description')
                        ->nullable()
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    
                    FileUpload::make('image')
                        ->nullable()
                        ->image()
                        ->directory('tags_images')
                        ->preserveFilenames()
                        ->fetchFileInformation(false)
                        ->columnSpanFull(),
                ])->columns(2)

            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Tag Details Section
                Infolists\Components\Section::make('Tag Details')
                    ->icon('heroicon-o-tag') 
                    ->headerActions([
                        Infolists\Components\Actions\Action::make('edit')
                            ->label('Edit Tag')
                            ->icon('heroicon-o-pencil')
                            ->url(fn($record) => TagResource::getUrl('edit', ['record' => $record]))
                            ->color('primary'),
                    ])
                    ->schema([
                        Infolists\Components\Section::make([
                            
                            Infolists\Components\Group::make([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Tag Name')
                                    ->weight('bold')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->color('primary')
                                    ->extraAttributes(['class' => 'bg-gradient-to-r from-primary-50 to-primary-100 p-3 rounded-lg']),
                                Infolists\Components\TextEntry::make('slug')
                                    ->label('Slug')
                                    ->icon('heroicon-o-link')
                                    ->copyable()
                                    ->copyMessage('Slug copied to clipboard!')
                                    ->badge()
                                    ->color('gray')
                                    ->extraAttributes(['class' => 'mt-2']),
                                Infolists\Components\TextEntry::make('number_of_products')
                                    ->label('Number of Products')
                                    ->icon('heroicon-o-queue-list')
                                    ->suffix(' products')
                                    ->numeric()
                                    ->formatStateUsing(fn($state) => number_format($state))
                                    ->badge()
                                    ->color('success')
                                    ->extraAttributes(['class' => 'mt-2']),
                            ])->columnSpan(7),
                            
                            Infolists\Components\Group::make([
                                Infolists\Components\ImageEntry::make('image')
                                    ->label('Tag Image')
                                    ->disk('public')
                                    ->height(220)
                                    ->width(220)
                                    ->square()
                                    ->defaultImageUrl(url('images/image-not-found-2.jpg'))
                                    ->extraImgAttributes(['class' => 'ring-4 ring-primary-200 shadow-lg']),
                            ])->columnSpan(5),

                        ])->columns(12), 

                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->icon('heroicon-o-document-text')
                            ->columnSpanFull()
                            ->markdown()
                            ->placeholder('No description provided')
                            ->extraAttributes(['class' => 'prose prose-sm max-w-none mt-4']),
                    ])
                    ->columns(3)
                    ->extraAttributes(['class' => 'bg-white rounded-xl shadow-sm']),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->extraImgAttributes(['class' => 'w-12 h-12 object-cover rounded-md'])
                    ->defaultImageUrl(url('images/image-not-found-2.jpg')),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn(?string $state): ?string => $state),
                TextColumn::make('number_of_products')
                    ->badge()
                    ->color('info')
                    ->suffix(' products')
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn(bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive'),
            ])
            ->filters([
                SelectFilter::make('is_active')
                ->label('Filter by Status')
                    ->options([
                        true => 'Active',
                        false => 'Inactive',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()->color('success'),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
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
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'view' => Pages\ViewTag::route('/{record}'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
