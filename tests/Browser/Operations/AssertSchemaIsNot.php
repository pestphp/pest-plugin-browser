<?php

declare(strict_types=1);

test('assert schema is not', function () {
    $url = 'https://laravel.com';

    $this->visit($url)
        ->assertSchemaIsNot('http');
});
