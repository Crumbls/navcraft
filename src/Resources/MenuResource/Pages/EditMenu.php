<?php

declare(strict_types=1);

namespace Crumbls\NavCraft\Resources\MenuResource\Pages;

use Crumbls\NavCraft\Forms\Components\TreeRelationField;
use Crumbls\NavCraft\Resources\MenuResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            ...MenuResource::getFormSchema($schema),
            TreeRelationField::make('items')
                ->hiddenLabel(),
        ]);
    }
}
