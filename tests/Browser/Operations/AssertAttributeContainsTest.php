<?php

declare(strict_types=1);

test('assert attribute contains', function () {
    $this->visit('/test/interactive-elements')
        ->assertSee('Foo');
    // ->assertAttributeContains('#i-have-data-testid', 'data-testid', 'to-be-seen');
})->only();

test('assert bar', function () {
    $this->visit('/test/interactive-elements')
        ->assertSee('Bar');
    // ->assertAttributeContains('#i-have-data-testid', 'data-testid', 'to-be-seen');
})->only();
