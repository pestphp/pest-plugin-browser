<?php

declare(strict_types=1);

test('assert path is', function () {
    $this->visit(playground()->url('/test/form-inputs'))
        ->assertPathIs('/test/form-inputs');
});

test('assert path is with wildcard', function () {
    $this->visit(playground()->url('/test/form-inputs'))
        ->assertPathIs('/test/*inputs');
});
