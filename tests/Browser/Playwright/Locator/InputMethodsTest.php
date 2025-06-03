<?php

declare(strict_types=1);

it('can select all text in input', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $input = $page->getByTestId('text-input');

    $input->fill('Select this text');
    $input->selectText();

    // After selecting text, typing should replace it
    $input->type('Replaced');
    expect($input->inputValue())->toBe('Replaced');
});

it('can set checked state explicitly', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $checkbox = $page->getByTestId('checkbox-input');

    // Set to checked
    $checkbox->setChecked(true);
    expect($checkbox->isChecked())->toBeTrue();

    // Set to unchecked
    $checkbox->setChecked(false);
    expect($checkbox->isChecked())->toBeFalse();

    // Set to checked again
    $checkbox->setChecked(true);
    expect($checkbox->isChecked())->toBeTrue();
});
