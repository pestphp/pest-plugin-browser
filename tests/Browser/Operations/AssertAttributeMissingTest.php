<?php

declare(strict_types=1);

test('assert attribute missing', function () {
    $this->visit('/test/interactive-elements')
        ->assertAttributeMissing('#i-have-data-testid', 'attr-that-does-not-exist-on-the-element');
});
