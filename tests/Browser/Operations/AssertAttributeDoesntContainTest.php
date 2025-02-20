<?php

declare(strict_types=1);

test('assert attribute doesnt contain', function () {
    $this->visit('/test/interactive-elements')
        ->assertAttributeContains('#i-have-data-testid', 'data-testid', 'not-to-be-seen');
});
