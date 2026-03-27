<?php

declare(strict_types=1);

namespace Crumbls\NavCraft\Resources\MenuResource\Pages;

use Crumbls\NavCraft\Resources\MenuResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;
}
