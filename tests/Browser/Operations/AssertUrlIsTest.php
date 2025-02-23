<?php

declare(strict_types=1);

test('assert url is', function (): void {
    $this->visit(playground()->url())
        ->assertUrlIs(playground()->url());
});
