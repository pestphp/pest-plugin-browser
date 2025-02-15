<?php

declare(strict_types=1);

arch('src')
    ->expect('Pest\Browser')
    ->toHaveMethodsDocumented()
    ->toHavePropertiesDocumented();

arch()->preset()->php();
arch()->preset()->strict();
arch()->preset()->security()->ignoring([
    'assert',
]);
