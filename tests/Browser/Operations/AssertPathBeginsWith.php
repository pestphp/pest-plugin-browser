<?php

declare(strict_types=1);

test('assert path starts with', function () {
    $url = 'https://laravel.com/docs/11.x';

    $this->visit($url)
        ->assertPathBeginsWith('/docs');
});
