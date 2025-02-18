<?php

declare(strict_types=1);

test('assert attribute missing', function () {
    $this->visit('/')
        ->assertAttributeMissing('main', 'data-test-attribute-missing');
});
