<?php

declare(strict_types=1);

namespace Crumbls\NavCraft\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Route;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'type',
        'label',
        'url',
        'route',
        'target',
        'order',
        'css_class',
        'icon',
        'content',
        'settings',
    ];

    public function getTable(): string
    {
        return config('navcraft.menu_items.table', 'navcraft_menu_items');
    }

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'settings' => 'array',
            'order' => 'integer',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }

    public function allDescendants(): HasMany
    {
        return $this->children()->with('allDescendants');
    }

    public function getUrl(): string
    {
        if ($this->type === 'route' && $this->route) {
            $params = $this->settings['route_params'] ?? [];

            if (Route::has($this->route)) {
                return route($this->route, $params);
            }

            return '#';
        }

        return $this->url ?? '#';
    }

    public function isOnActiveTrail(?string $currentUrl = null): bool
    {
        $currentUrl = $currentUrl ?? request()->url();

        if ($this->getUrl() === $currentUrl) {
            return true;
        }

        foreach ($this->children as $child) {
            if ($child->isOnActiveTrail($currentUrl)) {
                return true;
            }
        }

        return false;
    }

    public function getAncestorTrail(): array
    {
        $trail = [];
        $current = $this;

        while ($current) {
            array_unshift($trail, $current);
            $current = $current->parent;
        }

        return $trail;
    }

    protected static function newFactory()
    {
        return \Crumbls\NavCraft\Database\Factories\MenuItemFactory::new();
    }
}
