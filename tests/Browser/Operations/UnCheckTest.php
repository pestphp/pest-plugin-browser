<?php

declare(strict_types=1);

test('uncheck the checked checkbox', function () {
    $this->visit('http://localhost:9357')
        ->uncheck('input[name="checkbox-checked"]')
        ->assertNotChecked('input[name="checkbox-checked"]');
});
