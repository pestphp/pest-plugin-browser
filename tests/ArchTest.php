<?php

declare(strict_types=1);

arch()
    ->expect('Pest\Browser')
    ->toHaveMethodsDocumented()
    ->toHavePropertiesDocumented();

arch()
    ->expect('Pest\Browser\Operations\*')
    ->toBeTraits()
    ->toOnlyBeUsedIn('Pest\Browser\PendingTest');

arch()->preset()->php();
arch()->preset()->strict()->ignoring(['usleep']);
arch()->preset()->security()->ignoring(['assert', 'uniqid', 'parse_str']);
