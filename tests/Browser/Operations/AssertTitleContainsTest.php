<?php

declare(strict_types=1);

test('assert title contains', function () {
    $url = 'https://laravel.com';

    $browser = $this->visit($url);

    $browser->assertTitleContains('Laravel');
});
