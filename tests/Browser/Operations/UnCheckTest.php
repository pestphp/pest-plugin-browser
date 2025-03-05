<?php

declare(strict_types=1);

describe('uncheck', function () {
    it('unchecks the checked checkbox', function () {
        $this->visit(playgroundUrl('/test/form-inputs'))
            ->assertChecked('input[name="checked-checkbox"]')
            ->uncheck('input[name="checked-checkbox"]')
            ->assertNotChecked('input[name="checked-checkbox"]');
    });
});
