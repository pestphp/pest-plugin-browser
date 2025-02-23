<?php

declare(strict_types=1);

use Pest\Browser\Exceptions\BrowserOperationException;

describe('assert not present', function () {
    it('passes when the given element is not present', function () {
        $this->visit('/test/interactive-elements')
            ->assertNotPresent('#unexistent-element-for-sure-not-to-be-present');
    });

    it('fails when the given element is present', function () {
        $this->visit('/test/interactive-elements')
            ->assertNotPresent('#i-have-data-testid');
    })->throws(BrowserOperationException::class);

    it('fails when the given element is present, even when invisible', function () {
        $this->visit('/test/interactive-elements')
            ->assertNotPresent('#invisible-element'); // invisible, but present!
    })->throws(BrowserOperationException::class);
});
