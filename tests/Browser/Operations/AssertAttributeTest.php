<?php

declare(strict_types=1);

test('assert attribute', function () {
    $this->visit('/test/interactive-elements')
        ->assertAttribute('#i-have-data-testid', 'data-testid', 'i-exist-to-be-seen');
});
