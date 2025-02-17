<?php

declare(strict_types=1);

test('visit', function (): void {
    $this->visit('/')
        ->assertUrlIs('http://127.0.0.1:9357');
});
