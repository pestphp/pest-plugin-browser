<?php

declare(strict_types=1);

it('can check checkboxes', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $checkbox = $page->getByTestId('unchecked-checkbox');

    expect($checkbox->isChecked())->toBeFalse();

    $checkbox->check();

    expect($checkbox->isChecked())->toBeTrue();
});

it('can uncheck checkboxes', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $checkbox = $page->getByTestId('checked-checkbox');

    expect($checkbox->isChecked())->toBeTrue();

    $checkbox->uncheck();

    expect($checkbox->isChecked())->toBeFalse();
});
