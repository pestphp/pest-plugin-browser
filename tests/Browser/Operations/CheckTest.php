<?php

declare(strict_types=1);

test('check the unchecked checkbox', function () {
    $this->visit('http://localhost:9357')
        ->check('input[name="checkbox-unchecked"]')
        ->assertChecked('input[name="checkbox-unchecked"]');
});
