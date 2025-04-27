<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagResource\Pages;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Tables\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\{TextColumn, ImageColumn};
use Filament\Forms\Components\{TextInput, Select, Textarea, FileUpload, Toggle};
use Filament\Tables\Actions\{EditAction, DeleteAction, ViewAction, ForceDeleteAction, RestoreAction};

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
                        }),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(Tag::class, 'slug', ignoreRecord: true),
                    Textarea::make('description')
                        ->nullable()
                        ->maxLength(65535),

                    FileUpload::make('image')
                        ->nullable()
                        ->image()
                        ->directory('products/tag-images')
                        ->preserveFilenames()
                        ->fetchFileInformation(false),
                        
                    Toggle::make('is_active')
                        ->label('Active')
                        ->live()
                        ->dehydrateStateUsing(fn($state) => (bool) $state)
                        ->default(true),
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
                                    ->color('primary'),
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
                                Infolists\Components\TextEntry::make('is_active')
                                    ->badge()
                                    ->label('Status')
                                    ->color(fn(string $state): string => match ($state) {
                                        '1' => 'success',
                                        default => 'danger',
                                    })->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive'),
                            ])->columnSpan(7),

                            Infolists\Components\Group::make([
                                Infolists\Components\ImageEntry::make('image')
                                    ->label('Tag Image')
                                    ->disk('public')
                                    ->height(220)
                                    ->width(220)
                                    ->square()
                                    ->defaultImageUrl(url('images/inf-icon.png'))
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
                    ->defaultImageUrl(url('images/inf-icon.png')),
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
                TextColumn::make('number_of_products')->badge()->color('info'),
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
                    ViewAction::make()->color('success'),
                    EditAction::make(),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Tag')
                        ->modalDescription('Are you sure you want to delete this Tag? It will be moved to the trash.')
                        ->modalSubmitActionLabel('Confirm')
                        ->action(fn($record) => $record->delete())
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Tag Deleted')
                                ->body('The Tag has been moved to the trash.')
                        ),
                    RestoreAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Restore Tag')
                        ->modalDescription('Are you sure you want to restore this Tag?')
                        ->modalSubmitActionLabel('Confirm')
                        ->visible(fn($record) => $record->trashed())
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Tag Restored')
                                ->body('The Tag has been restored.')
                        ),
                    ForceDeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Tag')
                        ->modalDescription('Are you sure you want to permanently delete this Tag? This action cannot be undone.')
                        ->modalSubmitActionLabel('Confirm')
                        ->visible(fn($record) => $record->trashed())
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Tag Permanently Deleted')
                                ->body('The tag has been permanently deleted.')
                        ),
                ])
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
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

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withTrashed();
    }
}
