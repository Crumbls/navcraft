<?php

declare(strict_types=1);

return [
    'menus' => [
        'table' => 'navcraft_menus',
        'model' => \Crumbls\NavCraft\Models\Menu::class,
    ],
    'menu_items' => [
        'table' => 'navcraft_menu_items',
        'model' => \Crumbls\NavCraft\Models\MenuItem::class,
    ],
];
