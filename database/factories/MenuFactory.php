<?php

declare(strict_types=1);

namespace Crumbls\NavCraft\Database\Factories;

use Crumbls\NavCraft\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'status' => fake()->randomElement(['draft', 'published']),
            'settings' => [],
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => 'published']);
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function withItems(int $count = 3): static
    {
        return $this->afterCreating(function (Menu $menu) use ($count) {
            \Crumbls\NavCraft\Models\MenuItem::factory()
                ->count($count)
                ->sequence(fn ($sequence) => ['order' => $sequence->index])
                ->create(['menu_id' => $menu->id]);
        });
    }
}
