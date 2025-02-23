<?php

declare(strict_types=1);

use Pest\Browser\Exceptions\BrowserOperationException;

describe('assert missing', function () {
    it('passes when the given element is not visible', function () {
        $this->visit('/test/interactive-elements')
            ->assertMissing('#invisible-element');
    });

    it('fails when the given element is visible', function () {
        $this->visit('/test/interactive-elements')
            ->assertMissing('#i-have-data-testid');
    })->throws(BrowserOperationException::class);
});
