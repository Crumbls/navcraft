<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected function menusTable(): string
    {
        return config('navcraft.menus.table', 'navcraft_menus');
    }

    protected function tableName(): string
    {
        return config('navcraft.menu_items.table', 'navcraft_menu_items');
    }

    public function up(): void
    {
        Schema::create($this->tableName(), function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')
                ->constrained($this->menusTable())
                ->cascadeOnDelete();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained($this->tableName())
                ->nullOnDelete();
            $table->string('type')->default('link')->index();
            $table->string('label');
            $table->string('url')->nullable();
            $table->string('route')->nullable();
            $table->string('target')->default('_self');
            $table->integer('order')->default(0);
            $table->string('css_class')->nullable();
            $table->string('icon')->nullable();
            $table->json('content')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
