<?php

declare(strict_types=1);

use Pest\Browser\Exceptions\BrowserOperationException;

describe('assert present', function () {
    it('passes when the given element is present', function () {
        $this->visit('/test/interactive-elements')
            ->assertPresent('#i-have-data-testid');
    });

    it('passes when the given element is present - and invisible', function () {
        $this->visit('/test/interactive-elements')
            ->assertPresent('#invisible-element'); // invisible, but present
    });

    it('fails when the given element is not present', function () {
        $this->visit('/test/interactive-elements')
            ->assertPresent('#unexistent-element-for-sure-not-to-be-present');
    })->throws(BrowserOperationException::class);
});
