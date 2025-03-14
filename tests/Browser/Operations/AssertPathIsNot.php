<?php

declare(strict_types=1);

test('assert path is not', function () {
    $this->visit(playground()->url('/test/form-inputs'))
        ->assertPathIsNot('/test');
});
