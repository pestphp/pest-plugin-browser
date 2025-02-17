<?php

declare(strict_types=1);

test('visit', function (): void {
    $this->visit('/')
        ->assertUrlIs('http://localhost:9357');
});
