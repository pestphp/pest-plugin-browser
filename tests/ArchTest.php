<?php

declare(strict_types=1);

describe('arch', function () {
    arch()
        ->expect('Pest\Browser')
        ->toHaveMethodsDocumented()
        ->toHavePropertiesDocumented();

    arch()
        ->expect('Pest\Browser\Contracts')
        ->toBeInterfaces();

    arch()
        ->expect('Pest\Browser\Exceptions')
        ->toExtend('Throwable');

    arch('')
        ->expect('Pest\Browser\Operation\*')
        ->toBeClasses()
        ->toExtend('Pest\Browser\Operation')
        ->toImplement('Pest\Browser\Contracts\Operation')
        ->toOnlyBeUsedIn('Pest\Browser\PendingTest');

    arch()->preset()->php()->ignoring(['debug_backtrace']);
    arch()->preset()->strict()->ignoring(['Pest\Browser\Operation']);
    arch()->preset()->security()->ignoring(['assert']);
});
