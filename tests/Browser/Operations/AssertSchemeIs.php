<?php

declare(strict_types=1);

test('assert scheme', function () {
    $this->visit('http://localhost:9357')
        ->assertSchemeIs('http');
});
