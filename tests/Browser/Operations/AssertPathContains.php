<?php

declare(strict_types=1);

test('assert path contains', function () {
    $this->visit(playground()->url('/test/form-inputs'))
        ->assertPathContains('form');
});
