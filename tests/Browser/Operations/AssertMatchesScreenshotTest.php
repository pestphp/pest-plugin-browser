<?php

declare(strict_types=1);

test('assert page matches a screenshot', function () {
    $this->visit('https://example.com')
        ->screenshot('example.png')
        ->assertMatchesScreenshot('example.png');
});
