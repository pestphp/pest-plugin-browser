<?php

declare(strict_types=1);

test('assert attribute doesnt contain', function () {
    $url = 'https://laravel.com';

    $this->visit($url)
        ->assertAttributeDoesntContain('html', 'data-theme', 'not-here');
});
