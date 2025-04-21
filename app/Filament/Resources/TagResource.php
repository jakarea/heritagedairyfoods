<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagResource\Pages; 
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\{BadgeColumn};
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

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Tag Details')->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $old, ?string $state) {
                            if (($get('slug') ?? '') !== Str::slug($old)) {
                                return;
                            }
                            $set('slug', Str::slug($state));
                        }),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(Tag::class, 'slug', ignoreRecord: true),
                    Forms\Components\Textarea::make('description')
                        ->nullable()
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('image')
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
                    // ->collapsible()
                    ->headerActions([
                        Infolists\Components\Actions\Action::make('edit')
                            ->label('Edit Tag')
                            ->icon('heroicon-o-pencil')
                            ->url(fn($record) => TagResource::getUrl('edit', ['record' => $record]))
                            ->color('primary'),
                    ])
                    ->schema([
                        Infolists\Components\Split::make([
                            // Left Side: Name, Slug, Number of Products
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
                            ])->columnSpan(2),
                            // Right Side: Tag Image
                            Infolists\Components\Group::make([
                                Infolists\Components\ImageEntry::make('image')
                                    ->label('Tag Image')
                                    ->disk('public')
                                    ->height(120)
                                    ->width(120)
                                    ->circular()
                                    ->defaultImageUrl(url('images/placeholder.png'))
                                    ->extraImgAttributes(['class' => 'ring-4 ring-primary-200 shadow-lg']),
                            ])->columnSpan(1),
                        ])->from('lg')->grow(false),
                        // Description (Full Width)
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->icon('heroicon-o-document-text')
                            ->columnSpanFull()
                            ->markdown()
                            ->placeholder('No description provided')
                            ->extraAttributes(['class' => 'prose prose-sm max-w-none mt-4']),
                    ])
                    ->columns(3)
                    ->extraAttributes(['class' => 'bg-white rounded-xl shadow-sm border border-gray-200']),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image') 
                ->extraImgAttributes(['class' => 'w-12 h-12 object-cover rounded-md'])->defaultImageUrl(url('images/image-not-found-2.jpg')),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn(?string $state): ?string => $state),
                Tables\Columns\TextColumn::make('number_of_products')
                    ->sortable(), 
                BadgeColumn::make('created_at')->date(),
            ])
            ->filters([
                //
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
