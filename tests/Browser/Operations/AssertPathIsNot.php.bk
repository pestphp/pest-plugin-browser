<?php

declare(strict_types=1);

test('assert path is not', function () {
    $url = 'https://laravel.com/docs/11.x';

    $this->visit($url)
        ->assertPathIsNot('/docs/12.x');
});
