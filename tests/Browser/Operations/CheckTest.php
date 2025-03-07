<?php

declare(strict_types=1);

describe('check', function () {
    it('checks the unchecked checkbox', function () {
        $this->visit(playgroundUrl('/test/form-inputs'))
            ->check('input[name="default-checkbox"]')
            ->assertChecked('input[name="default-checkbox"]');
    });
});
