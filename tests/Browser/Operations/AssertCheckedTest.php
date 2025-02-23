<?php

declare(strict_types=1);

test('assert checkbox is checked', function () {
    $this->visit(playground()->url('/test/form-inputs'))
        ->assertChecked('input[name="checked-checkbox"]');
});
