<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('toBeChecked', function (): void {
    it('passes when checkbox is checked', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="checked-checkbox"]'))->toBeChecked();
    });

    it('fails when checkbox is checked', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="checked-checkbox"]'))->not->toBeChecked();
    })->throws(ExpectationFailedException::class);

    it('passes when checkbox is not checked', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="unchecked-checkbox"]'))->not->toBeChecked();
    });

    it('fails when checkbox is not checked', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="unchecked-checkbox"]'))->toBeChecked();
    })->throws(ExpectationFailedException::class);
});

describe('toBeVisible', function (): void {
    it('passes when element is visible', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="visible"]'))->toBeVisible();
    });

    it('fails when element is visible', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="visible"]'))->not->toBeVisible();
    })->throws(ExpectationFailedException::class);

    it('passes when element is not visible', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="hidden"]'))->not->toBeVisible();
    });

    it('fails when element is not visible', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="hidden"]'))->toBeVisible();
    })->throws(ExpectationFailedException::class);
});

describe('toBeHidden', function (): void {
    it('passes when element is hidden', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="hidden"]'))->toBeHidden();
    });

    it('fails when element is hidden', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="hidden"]'))->not->toBeHidden();
    })->throws(ExpectationFailedException::class);

    it('passes when element is not hidden', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="visible"]'))->not->toBeHidden();
    });

    it('fails when element is not hidden', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="visible"]'))->toBeHidden();
    })->throws(ExpectationFailedException::class);
});

describe('toBeEnabled', function (): void {
    it('passes when input is enabled', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="enabled-input"]'))->toBeEnabled();
    });

    it('fails when input is enabled', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="enabled-input"]'))->not->toBeEnabled();
    })->throws(ExpectationFailedException::class);

    it('passes when input is not enabled', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="disabled-input"]'))->not->toBeEnabled();
    });

    it('fails when input is not enabled', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="disabled-input"]'))->toBeEnabled();
    })->throws(ExpectationFailedException::class);

    it('passes when button is enabled', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('button[name="enabled-button"]'))->toBeEnabled();
    });

    it('passes when button is not enabled', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('button[name="disabled-button"]'))->not->toBeEnabled();
    });
});

describe('toBeDisabled', function (): void {
    it('passes when input is disabled', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="disabled-input"]'))->toBeDisabled();
    });

    it('fails when input is disabled', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="disabled-input"]'))->not->toBeDisabled();
    })->throws(ExpectationFailedException::class);

    it('passes when input is not disabled', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="enabled-input"]'))->not->toBeDisabled();
    });

    it('fails when input is not disabled', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="enabled-input"]'))->toBeDisabled();
    })->throws(ExpectationFailedException::class);

    it('passes when button is disabled', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('button[name="disabled-button"]'))->toBeDisabled();
    });

    it('passes when checkbox is disabled', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="disabled-checkbox"]'))->toBeDisabled();
    });
});

describe('toBeEditable', function (): void {
    it('passes when input is editable', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="enabled-input"]'))->toBeEditable();
    });

    it('fails when input is editable', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="enabled-input"]'))->not->toBeEditable();
    })->throws(ExpectationFailedException::class);

    it('passes when input is not editable (readonly)', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="readonly-input"]'))->not->toBeEditable();
    });

    it('fails when input is not editable (readonly)', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="readonly-input"]'))->toBeEditable();
    })->throws(ExpectationFailedException::class);

    it('passes when input is not editable (disabled)', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="disabled-input"]'))->not->toBeEditable();
    });

    it('fails when input is not editable (disabled)', function (): void {
        $page = $this->page()->goto(playgroundUrl('/test/form-inputs'));

        expect($page->locator('input[name="disabled-input"]'))->toBeEditable();
    })->throws(ExpectationFailedException::class);
});
