<?php

declare(strict_types=1);

namespace Crumbls\NavCraft\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'settings',
    ];

    public function getTable(): string
    {
        return config('navcraft.menus.table', 'navcraft_menus');
    }

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('order');
    }

    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('order');
    }

    protected static function newFactory()
    {
        return \Crumbls\NavCraft\Database\Factories\MenuFactory::new();
    }
}
