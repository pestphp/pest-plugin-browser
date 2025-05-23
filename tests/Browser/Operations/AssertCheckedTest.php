<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertChecked', function (): void {
    it('passes when checkbox is checked', function (): void {
        $page = $this->page(playgroundUrl('/test/form-inputs'));

        expect($page->querySelector('input[name="checked-checkbox"]'))->toBeChecked();
    });

    it('fails when checkbox is checked', function (): void {
        $page = $this->page(playgroundUrl('/test/form-inputs'));

        expect($page->querySelector('input[name="checked-checkbox"]'))->not->toBeChecked();
    })->throws(ExpectationFailedException::class);

    it('passes when checkbox is not checked', function (): void {
        $page = $this->page(playgroundUrl('/test/form-inputs'));

        expect($page->querySelector('input[name="unchecked-checkbox"]'))->not->toBeChecked();
    });

    it('fails when checkbox is not checked', function (): void {
        $page = $this->page(playgroundUrl('/test/form-inputs'));

        expect($page->querySelector('input[name="unchecked-checkbox"]'))->toBeChecked();
    })->throws(ExpectationFailedException::class);
});
