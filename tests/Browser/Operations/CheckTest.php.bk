<?php

declare(strict_types=1);

test('check the unchecked checkbox', function () {
    $this->visit('/test/form-inputs')
        ->check('input[name="default-checkbox"]')
        ->assertChecked('input[name="default-checkbox"]');
});
