<?php

declare(strict_types=1);

test('assert that the given input field does not have the given value', function () {
    $this->visit('/test/form-inputs')
        ->assertInputValueIsNot('#email', 'jane.doe@pestphp.com');
});
