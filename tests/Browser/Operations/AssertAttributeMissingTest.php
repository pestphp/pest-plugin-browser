<?php

declare(strict_types=1);

test('assert attribute missing', function () {
    $url = 'https://laravel.com';

    $this->visit($url)
        ->assertAttributeMissing('html', 'data-test-attribute-missing');
});
