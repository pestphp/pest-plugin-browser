<?php

declare(strict_types=1);

test('assert element has attribute that matches expected content', function () {
    $url = 'https://laravel.com';

    $this->visit($url)
        ->assertAttribute('html', 'data-theme', 'light');

});
