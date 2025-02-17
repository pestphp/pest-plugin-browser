<?php

declare(strict_types=1);

test('assert attribute contains', function () {
    $url = 'https://laravel.com';

    $this->visit($url)
        ->assertAttributeContains('html', 'data-theme', 'ight');

});
