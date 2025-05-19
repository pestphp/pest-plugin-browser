<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;
use function Pest\Browser\page;

describe('assertChecked', function () {
    it('passes when checkbox is checked', function () {
        $page = page(playgroundUrl('/test/form-inputs'));

        expect($page->querySelector('input[name="checked-checkbox"]'))->toBeChecked();
    });

    it('fails when checkbox is not checked', function () {
        $page = page(playgroundUrl('/test/form-inputs'));

        expect($page->querySelector('input[name="unchecked-checkbox"]'))->toBeChecked();
    })->throws(ExpectationFailedException::class);
});
