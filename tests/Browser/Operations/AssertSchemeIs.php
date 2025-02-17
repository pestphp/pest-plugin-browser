<?php

declare(strict_types=1);

test('assert scheme', function () {
    $this->visit('http://localhost')
        ->assertSchemeIs('http');
});
