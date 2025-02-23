<?php

declare(strict_types=1);

test('assert scheme', function () {
    $this->visit(playground()->url())
        ->assertSchemeIs('http');
});
