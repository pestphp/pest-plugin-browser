<?php

declare(strict_types=1);

use Pest\Browser\ServerManager;

test('route helper', function (): void {
    $route = route('auth');

    page();

    $url = ServerManager::instance()->http()->url();

    expect($route)->toBe($url.'/auth');
});
