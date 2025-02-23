<?php

declare(strict_types=1);

test('assert host is not', function () {
    $this->visit(playground()->url())
        ->assertHostIsNot('example.com');
});
