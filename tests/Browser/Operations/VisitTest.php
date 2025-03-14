<?php

declare(strict_types=1);

test('visit', function (): void {
    $this->visit(playground()->url())
        ->assertUrlIs(playground()->url());
});
