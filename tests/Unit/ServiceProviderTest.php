<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('registers navcraft blade directive', function () {
    $directives = Blade::getCustomDirectives();

    expect($directives)->toHaveKey('navcraft');
});

it('registers navCraftScripts blade directive', function () {
    $directives = Blade::getCustomDirectives();

    expect($directives)->toHaveKey('navCraftScripts');
});

it('loads navcraft views namespace', function () {
    $finder = app('view')->getFinder();
    $hints = $finder->getHints();

    expect($hints)->toHaveKey('navcraft');
});
