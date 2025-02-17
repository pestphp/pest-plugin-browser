<?php

declare(strict_types=1);

test('assert url is', function (): void {
    $this->visit('/')
        ->assertUrlIs('http://127.0.0.1:9357');
});
