<?php

declare(strict_types=1);

use Pest\Browser\Exceptions\BrowserOperationException;

describe('assert visible', function () {
    it('passes when the given element is visible', function () {
        $this->visit('/test/interactive-elements')
            ->assertVisible('#i-have-data-testid');
    });

    it('fails when the given element is not visible', function () {
        $this->visit('/test/interactive-elements')
            ->assertVisible('#invisible-element');
    })->throws(BrowserOperationException::class);
});
