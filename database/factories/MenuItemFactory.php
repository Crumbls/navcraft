<?php

declare(strict_types=1);

namespace Crumbls\NavCraft\Database\Factories;

use Crumbls\NavCraft\Models\Menu;
use Crumbls\NavCraft\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'type' => 'link',
            'label' => fake()->words(2, true),
            'url' => fake()->url(),
            'target' => '_self',
            'order' => 0,
        ];
    }

    public function megamenu(): static
    {
        return $this->state([
            'type' => 'megamenu',
            'url' => null,
            'content' => ['rows' => []],
        ]);
    }

    public function divider(): static
    {
        return $this->state([
            'type' => 'divider',
            'label' => '',
            'url' => null,
        ]);
    }

    public function heading(): static
    {
        return $this->state([
            'type' => 'heading',
            'url' => null,
        ]);
    }

    public function withContent(array $content): static
    {
        return $this->state(['content' => $content]);
    }

    public function childOf(MenuItem $parent): static
    {
        return $this->state([
            'menu_id' => $parent->menu_id,
            'parent_id' => $parent->id,
        ]);
    }
}
