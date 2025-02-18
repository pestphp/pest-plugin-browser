<?php

declare(strict_types=1);

test('assert attribute contains', function () {
    $this->visit('/test/interactive-elements')
        ->assertAttributeContains('#i-have-data-testid', 'data-testid', 'to-be-seen');
});
