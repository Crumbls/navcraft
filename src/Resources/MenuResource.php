<?php

declare(strict_types=1);

namespace Crumbls\NavCraft\Resources;

use BackedEnum;
use Crumbls\NavCraft\Models\Menu;
use Crumbls\NavCraft\Models\MenuItem;
use Crumbls\NavCraft\Resources\MenuResource\Pages;
use Crumbls\NavCraft\Resources\MenuResource\RelationManagers\MenuItemsRelationManager;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class MenuResource extends Resource
{
    protected static ?string $model = null;

    public static function getModel(): string
    {
        return config('navcraft.menus.model', Menu::class);
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bars-3';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?string $navigationLabel = null;

    public static function getNavigationLabel(): string
    {
        return 'Menus';
    }

    protected static ?string $slug = 'menus';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(static::getFormSchema($schema));
    }

	public static function getFormSchema(Schema $schema) : array {
		return [
			Section::make('info')
			->schema([

				TextInput::make('name')
					->required()
					->maxLength(255)
					->live(onBlur: true)
					->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

				TextInput::make('slug')
					->required()
					->maxLength(255)
					->unique(ignoreRecord: true),

				TextInput::make('description')
					->maxLength(500),

				Select::make('status')
					->options([
						'draft' => 'Draft',
						'published' => 'Published',
					])
					->default('draft')
					->required()
			])
		];
	}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->searchable(),

                TextColumn::make('all_items_count')
                    ->counts('allItems')
                    ->label('Items'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'published' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->requiresConfirmation()
                    ->modalHeading('Duplicate Menu')
                    ->modalDescription('This will create a copy of the menu with all its items.')
                    ->action(function (Menu $record): void {
                        $clone = $record->replicate(['all_items_count']);
                        $clone->name = $record->name . ' (copy)';
                        $clone->slug = $record->slug . '-copy-' . now()->timestamp;
                        $clone->status = 'draft';
                        $clone->save();

                        $itemMap = [];

                        $record->allItems()->orderBy('order')->each(function (MenuItem $item) use ($clone, &$itemMap): void {
                            $newItem = $item->replicate();
                            $newItem->menu_id = $clone->id;
                            $newItem->parent_id = $item->parent_id ? ($itemMap[$item->parent_id] ?? null) : null;
                            $newItem->save();

                            $itemMap[$item->id] = $newItem->id;
                        });
                    }),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
//            MenuItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
