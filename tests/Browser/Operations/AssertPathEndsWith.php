<?php

declare(strict_types=1);

test('assert path ends with', function () {
    $this->visit(playground()->url('/test/form-inputs'))
        ->assertPathEndsWith('/form-inputs');
});
