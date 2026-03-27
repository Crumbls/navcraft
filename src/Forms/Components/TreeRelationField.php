<?php

declare(strict_types=1);

namespace Crumbls\NavCraft\Forms\Components;

use Closure;
use Crumbls\Layup\Forms\Components\LayupBuilder;
use Crumbls\NavCraft\Models\MenuItem;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Route;

class TreeRelationField extends Field
{
    protected string $view = 'navcraft::forms.components.tree-relation-field';

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerActions([
            $this->editItemAction(),
            $this->addItemAction(),
            $this->addChildItemAction(),
            $this->deleteItemAction(),
            $this->duplicateItemAction(),
            $this->reorderItemsAction(),
            $this->renameItemAction(),
            $this->moveItemAction(),
        ]);

        $this->dehydrated(false);
    }

    public function getViewData(): array
    {
        $data = parent::getViewData();
        $record = $this->getRecord();

        if ($record && $record->exists) {
            $items = $record->allItems()->orderBy('order')->get();
            $data['initialItems'] = $this->buildTree($items);
            $data['maxId'] = $items->max('id') ?? 0;
        } else {
            $data['initialItems'] = [];
            $data['maxId'] = 0;
        }

        $itemTypes = config('navcraft.item_types', []);
        $data['typeLabels'] = collect($itemTypes)->mapWithKeys(
            fn (array $config, string $key): array => [$key => $config['label'] ?? $key]
        )->all();
        $data['supportsChildren'] = collect($itemTypes)->filter(
            fn (array $config): bool => $config['supports_children'] ?? true
        )->keys()->all();

        return $data;
    }

    protected function buildTree(Collection $items, ?int $parentId = null): array
    {
        return $items->where('parent_id', $parentId)
            ->values()
            ->map(fn (MenuItem $item): array => [
                'id' => $item->id,
                'label' => $item->label,
                'type' => $item->type ?? 'url',
                'url' => $item->url ?? '',
                'route_name' => $item->route ?? '',
                'route_params' => $item->settings['route_params'] ?? [],
                'mega_content' => $item->content ?? ['rows' => []],
                'target' => $item->target ?? '_self',
                'css_class' => $item->css_class ?? '',
                'icon' => $item->icon ?? '',
                'children' => $this->buildTree($items, $item->id),
            ])
            ->all();
    }

    public function editItemAction(): Action
    {
        return Action::make('editItem')
            ->label('Edit Item')
            ->slideOver()
            ->fillForm(function (array $arguments): array {
                $item = MenuItem::find($arguments['id'] ?? 0);

                if (! $item) {
                    return [];
                }

                return [
                    'label' => $item->label,
                    'type' => $item->type ?? 'url',
                    'url' => $item->url ?? '',
                    'route_name' => $item->route ?? '',
                    'route_params' => $item->settings['route_params'] ?? [],
                    'mega_content' => $item->content ?? ['rows' => []],
                    'open_in_new_tab' => ($item->target ?? '_self') === '_blank',
                    'css_class' => $item->css_class ?? '',
                    'icon' => $item->icon ?? '',
                ];
            })
            ->schema([
                Tabs::make('item_tabs')
                    ->tabs([
                        Tabs\Tab::make('Content')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                TextInput::make('label')
                                    ->label('Label')
                                    ->required(),

                                Select::make('type')
                                    ->label('Type')
                                    ->options(
                                        collect(config('navcraft.item_types', []))
                                            ->mapWithKeys(fn (array $c, string $k): array => [$k => $c['label'] ?? $k])
                                            ->all()
                                    )
                                    ->default('url')
                                    ->required()
                                    ->live(),

                                TextInput::make('url')
                                    ->label('URL')
                                    ->placeholder('/about or https://example.com')
                                    ->regex('/^(\/|https?:\/\/)/')
                                    ->visible(fn (Get $get): bool => $get('type') === 'url')
                                    ->required(fn (Get $get): bool => $get('type') === 'url'),

                                Toggle::make('open_in_new_tab')
                                    ->label('Open in new tab')
                                    ->visible(fn (Get $get): bool => in_array($get('type'), ['url', 'route'])),

                                Select::make('route_name')
                                    ->label('Route')
                                    ->options(fn (): array => static::getNamedRoutes())
                                    ->searchable()
                                    ->visible(fn (Get $get): bool => $get('type') === 'route')
                                    ->required(fn (Get $get): bool => $get('type') === 'route')
                                    ->live()
                                    ->afterStateUpdated(fn ($state, callable $set) => $set(
                                        'route_params',
                                        collect(static::getRouteParameters($state))
                                            ->mapWithKeys(fn (string $param) => [$param => ''])
                                            ->all()
                                    )),

                                Placeholder::make('route_params_hint')
                                    ->label('Route Parameters')
                                    ->content(fn (Get $get): string => match (true) {
                                        ! $get('route_name') => 'Select a route first.',
                                        empty(static::getRouteParameters($get('route_name'))) => 'This route has no parameters.',
                                        default => 'Fill in the parameter values below.',
                                    })
                                    ->visible(fn (Get $get): bool => $get('type') === 'route'),

                                KeyValue::make('route_params')
                                    ->label('Parameters')
                                    ->keyLabel('Parameter')
                                    ->valueLabel('Value')
                                    ->addable(false)
                                    ->deletable(false)
                                    ->visible(fn (Get $get): bool => $get('type') === 'route'
                                        && $get('route_name')
                                        && ! empty(static::getRouteParameters($get('route_name')))
                                    ),

                                LayupBuilder::make('mega_content')
                                    ->label('Mega Menu Content')
                                    ->columnSpanFull()
                                    ->visible(fn (Get $get): bool => $get('type') === 'mega'),
                            ]),

                        Tabs\Tab::make('Appearance')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                TextInput::make('css_class')
                                    ->label('CSS Classes')
                                    ->placeholder('e.g. text-red-500 font-bold'),

                                TextInput::make('icon')
                                    ->label('Icon')
                                    ->placeholder('e.g. heroicon-o-home'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->action(function (array $data, array $arguments): void {
                $id = (int) ($arguments['id'] ?? 0);

                if (! $id) {
                    return;
                }

                $item = MenuItem::find($id);

                if (! $item) {
                    return;
                }

                $item->update([
                    'label' => $data['label'],
                    'type' => $data['type'],
                    'url' => $data['type'] === 'url' ? ($data['url'] ?? null) : null,
                    'route' => $data['type'] === 'route' ? ($data['route_name'] ?? null) : null,
                    'content' => $data['type'] === 'mega' ? ($data['mega_content'] ?? null) : null,
                    'target' => ! empty($data['open_in_new_tab']) ? '_blank' : '_self',
                    'css_class' => $data['css_class'] ?? null,
                    'icon' => $data['icon'] ?? null,
                    'settings' => array_merge($item->settings ?? [], [
                        'route_params' => $data['route_params'] ?? [],
                    ]),
                ]);

                $this->getLivewire()->dispatch(
                    'nc-tree-item-updated',
                    id: $id,
                    data: array_merge($data, [
                        'target' => ! empty($data['open_in_new_tab']) ? '_blank' : '_self',
                    ]),
                );
            });
    }

    public function addItemAction(): Action
    {
        return Action::make('addItem')
            ->label('Add Item')
            ->action(function (array $arguments): void {
                $record = $this->getRecord();

                if (! $record) {
                    return;
                }

                $maxOrder = $record->allItems()->max('order') ?? -1;

                $item = $record->allItems()->create([
                    'label' => 'New Item',
                    'type' => 'url',
                    'order' => $maxOrder + 1,
                ]);

                $this->getLivewire()->dispatch('nc-tree-item-added', item: [
                    'id' => $item->id,
                    'label' => $item->label,
                    'type' => $item->type,
                    'url' => '',
                    'route_name' => '',
                    'route_params' => [],
                    'mega_content' => ['rows' => []],
                    'target' => '_self',
                    'css_class' => '',
                    'icon' => '',
                    'children' => [],
                ]);
            });
    }

    public function addChildItemAction(): Action
    {
        return Action::make('addChildItem')
            ->label('Add Child Item')
            ->action(function (array $arguments): void {
                $parentId = (int) ($arguments['parentId'] ?? 0);

                if (! $parentId) {
                    return;
                }

                $parent = MenuItem::find($parentId);

                if (! $parent) {
                    return;
                }

                $maxOrder = MenuItem::where('parent_id', $parentId)->max('order') ?? -1;

                $item = MenuItem::create([
                    'menu_id' => $parent->menu_id,
                    'parent_id' => $parentId,
                    'label' => 'New Child',
                    'type' => 'url',
                    'order' => $maxOrder + 1,
                ]);

                $this->getLivewire()->dispatch('nc-tree-item-child-added', parentId: $parentId, item: [
                    'id' => $item->id,
                    'label' => $item->label,
                    'type' => $item->type,
                    'url' => '',
                    'route_name' => '',
                    'route_params' => [],
                    'mega_content' => ['rows' => []],
                    'target' => '_self',
                    'css_class' => '',
                    'icon' => '',
                    'children' => [],
                ]);
            });
    }

    public function moveItemAction(): Action
    {
        return Action::make('moveItem')
            ->action(function (array $arguments): void {
                $id = (int) ($arguments['id'] ?? 0);
                $direction = $arguments['direction'] ?? null;

                if (! $id || ! in_array($direction, ['up', 'down'])) {
                    return;
                }

                $item = MenuItem::find($id);

                if (! $item) {
                    return;
                }

                $siblings = MenuItem::where('menu_id', $item->menu_id)
                    ->where('parent_id', $item->parent_id)
                    ->orderBy('order')
                    ->get();

                $index = $siblings->search(fn (MenuItem $s): bool => $s->id === $item->id);

                if ($index === false) {
                    return;
                }

                $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

                if ($swapIndex < 0 || $swapIndex >= $siblings->count()) {
                    return;
                }

                $swapItem = $siblings[$swapIndex];
                $tempOrder = $item->order;
                $item->update(['order' => $swapItem->order]);
                $swapItem->update(['order' => $tempOrder]);

                $this->getLivewire()->dispatch('nc-tree-reorder-complete');
            });
    }

    public function deleteItemAction(): Action
    {
        return Action::make('deleteItem')
            ->requiresConfirmation()
            ->modalHeading('Delete Item')
            ->modalDescription('Are you sure you want to delete this item and all of its children?')
            ->modalIcon('heroicon-o-exclamation-triangle')
            ->modalIconColor('danger')
            ->color('danger')
            ->action(function (array $arguments): void {
                $id = (int) ($arguments['id'] ?? 0);

                if (! $id) {
                    return;
                }

                $item = MenuItem::find($id);

                if (! $item) {
                    return;
                }

                $item->delete();

                $this->getLivewire()->dispatch('nc-tree-item-deleted', id: $id);
            });
    }

    public function duplicateItemAction(): Action
    {
        return Action::make('duplicateItem')
            ->label('Duplicate Item')
            ->action(function (array $arguments): void {
                $id = (int) ($arguments['id'] ?? 0);

                if (! $id) {
                    return;
                }

                $original = MenuItem::find($id);

                if (! $original) {
                    return;
                }

                $clone = $this->deepCloneItem($original, $original->parent_id);

                $tree = $this->buildTree(
                    MenuItem::where('id', $clone->id)
                        ->orWhere(function ($q) use ($clone) {
                            $this->getDescendantIds($clone->id, $q);
                        })
                        ->orderBy('order')
                        ->get(),
                    $clone->parent_id
                );

                $this->getLivewire()->dispatch('nc-tree-item-duplicated', item: $tree[0] ?? []);
            });
    }

    protected function deepCloneItem(MenuItem $original, ?int $parentId): MenuItem
    {
        $clone = $original->replicate();
        $clone->parent_id = $parentId;
        $clone->label = $original->label . ' (copy)';
        $clone->order = ($original->order ?? 0) + 1;
        $clone->save();

        foreach ($original->children as $child) {
            $this->deepCloneItem($child, $clone->id);
        }

        return $clone;
    }

    protected function getDescendantIds(int $parentId, $query): void
    {
        $childIds = MenuItem::where('parent_id', $parentId)->pluck('id');

        if ($childIds->isEmpty()) {
            return;
        }

        $query->whereIn('id', $childIds);

        foreach ($childIds as $childId) {
            $query->orWhere(function ($q) use ($childId) {
                $this->getDescendantIds($childId, $q);
            });
        }
    }

    public function renameItemAction(): Action
    {
        return Action::make('renameItem')
            ->action(function (array $arguments): void {
                $id = (int) ($arguments['id'] ?? 0);
                $label = trim($arguments['label'] ?? '');

                if (! $id || $label === '') {
                    return;
                }

                MenuItem::where('id', $id)->update(['label' => $label]);

                $this->getLivewire()->dispatch('nc-tree-item-renamed', id: $id, label: $label);
            });
    }

    public function reorderItemsAction(): Action
    {
        return Action::make('reorderItems')
            ->action(function (array $arguments): void {
                $order = 0;
                $this->persistTree($arguments['items'] ?? [], null, $order);
            });
    }

    protected function persistTree(array $items, ?int $parentId, int &$order): void
    {
        foreach ($items as $item) {
            MenuItem::where('id', $item['id'])->update([
                'parent_id' => $parentId,
                'order' => $order++,
            ]);

            $this->persistTree($item['children'] ?? [], (int) $item['id'], $order);
        }
    }

    public static function getNamedRoutes(): array
    {
        return collect(Route::getRoutes()->getRoutesByName())
            ->filter(fn ($route): bool => ! str_starts_with($route->getName(), 'filament.')
                && ! str_starts_with($route->getName(), 'livewire.')
                && ! str_starts_with($route->getName(), 'ignition.')
            )
            ->mapWithKeys(fn ($route, string $name): array => [
                $name => "{$name}  ({$route->uri()})",
            ])
            ->sort()
            ->all();
    }

    public static function getRouteParameters(?string $routeName): array
    {
        if (! $routeName) {
            return [];
        }

        $route = Route::getRoutes()->getByName($routeName);

        if (! $route) {
            return [];
        }

        preg_match_all('/\{(\w+?)\??}/', $route->uri(), $matches);

        return $matches[1] ?? [];
    }
}
