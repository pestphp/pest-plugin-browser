<?php

declare(strict_types=1);

test('assert that the given input field has the given value', function () {
    $this->visit('/test/form-inputs')
        ->assertInputValue('#email', 'john.doe@pestphp.com');
});
