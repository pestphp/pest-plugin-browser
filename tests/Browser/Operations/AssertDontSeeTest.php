<?php

declare(strict_types=1);

test('assert does not see', function () {
    $url = 'https://laravel.com';

    $this->visit($url)
        ->assertDontSee('The PHP Framework For Artisans test');
});

test('assert does not see ignoring case', function () {
    $url = 'https://laravel.com';

    $this->visit($url)
        ->assertDontSee('the php framework for artisans test', true);
});
