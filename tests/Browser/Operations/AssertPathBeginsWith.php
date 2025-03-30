<?php

declare(strict_types=1);

test('assert path starts with', function () {
    $this->visit(playground()->url('/test/form-inputs'))
        ->assertPathBeginsWith('/test');
});
