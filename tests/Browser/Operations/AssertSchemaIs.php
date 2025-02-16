<?php

declare(strict_types=1);

test('assert schema', function () {
    $url = 'https://laravel.com';

    $this->visit($url)
        ->assertSchemaIs('https');
});
