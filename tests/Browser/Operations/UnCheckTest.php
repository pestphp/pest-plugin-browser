<?php

declare(strict_types=1);

test('uncheck the checked checkbox', function () {
    $this->visit(playground()->url('/test/form-inputs'))
        ->uncheck('input[name="checked-checkbox"]')
        ->assertNotChecked('input[name="checked-checkbox"]');
});
