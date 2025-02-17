<?php

declare(strict_types=1);

test('assert attribute contains', function () {
    $this->visit('/')
        ->assertAttributeContains('main', 'id', 'pp');
});
