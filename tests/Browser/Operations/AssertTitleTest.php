<?php

declare(strict_types=1);

test('assert title', function (): void {
    $this->visit('/')
        ->assertTitle('Pest Plugin Browser - Playground');
});
