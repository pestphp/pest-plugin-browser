<?php

declare(strict_types=1);

use PHPUnit\Util\ExcludeList;

it('excludes the plugin source directory from failure traces', function (): void {
    $source = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Plugin.php';

    expect((new ExcludeList(true))->isExcluded($source))->toBeTrue();
});
