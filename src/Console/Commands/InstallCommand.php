<?php

declare(strict_types=1);

namespace Crumbls\NavCraft\Console\Commands;

use Crumbls\NavCraft\Models\Menu;
use Crumbls\NavCraft\Models\MenuItem;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'navcraft:install {--seed : Create a starter menu with sample items}';

    protected $description = 'Install NavCraft: publish config and views, run migrations, and optionally seed a starter menu.';

    public function handle(): int
    {
        $this->info('Installing NavCraft...');

        $this->call('vendor:publish', [
            '--tag' => 'navcraft-config',
            '--force' => false,
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'navcraft-views',
            '--force' => false,
        ]);

        $this->call('migrate');

        if ($this->option('seed') || $this->confirm('Create a starter menu with sample items?', true)) {
            $this->seedStarterMenu();
        }

        $this->newLine();
        $this->info('NavCraft installed successfully.');
        $this->newLine();
        $this->comment('Next steps:');
        $this->line('  1. Register NavCraftPlugin in your Filament panel provider');
        $this->line('  2. Add @navCraftScripts and @navcraft(\'main-navigation\') to your layout');
        $this->line('  3. Visit the Menus section in your Filament panel');

        return self::SUCCESS;
    }

    protected function seedStarterMenu(): void
    {
        if (Menu::where('slug', 'main-navigation')->exists()) {
            $this->warn('A menu with slug "main-navigation" already exists. Skipping seed.');

            return;
        }

        $menu = Menu::create([
            'name' => 'Main Navigation',
            'slug' => 'main-navigation',
            'description' => 'Primary site navigation',
            'status' => 'published',
            'settings' => [
                'theme' => 'minimal',
                'sticky' => false,
                'hover_mode' => 'click',
            ],
        ]);

        $home = MenuItem::create([
            'menu_id' => $menu->id,
            'label' => 'Home',
            'type' => 'url',
            'url' => '/',
            'order' => 0,
        ]);

        $about = MenuItem::create([
            'menu_id' => $menu->id,
            'label' => 'About',
            'type' => 'url',
            'url' => '/about',
            'order' => 1,
        ]);

        MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $about->id,
            'label' => 'Our Team',
            'type' => 'url',
            'url' => '/about/team',
            'order' => 0,
        ]);

        MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $about->id,
            'label' => 'Contact',
            'type' => 'url',
            'url' => '/contact',
            'order' => 1,
        ]);

        MenuItem::create([
            'menu_id' => $menu->id,
            'label' => 'Services',
            'type' => 'url',
            'url' => '/services',
            'order' => 2,
        ]);

        $this->info('Starter menu "Main Navigation" created with ' . $menu->allItems()->count() . ' items.');
    }
}
