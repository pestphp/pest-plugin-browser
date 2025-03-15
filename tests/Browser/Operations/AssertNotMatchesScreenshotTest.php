<?php

declare(strict_types=1);

test('assert page not matches a screenshot', function () {
    $this->visit('https://example.com')
        ->screenshot('example.png')
        ->assertMatchesScreenshot('example.png');

    $this->visit('https://laravel.com')
        ->assertNotMatchesScreenshot('example.png');
});
