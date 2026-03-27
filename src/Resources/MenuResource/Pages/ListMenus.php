<?php

declare(strict_types=1);

namespace Crumbls\NavCraft\Resources\MenuResource\Pages;

use Crumbls\NavCraft\Resources\MenuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMenus extends ListRecords
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
