<?php

declare(strict_types=1);

test('assert checkbox is not checked', function () {
    $this->visit(playground()->url('/test/form-inputs'))
        ->assertNotChecked('input[name="default-checkbox"]');
});
