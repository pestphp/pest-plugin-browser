<?php

declare(strict_types=1);

it('can fill input fields', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $input = $page->getByTestId('text-input');

    $input->fill('Hello World');

    expect($input->inputValue())->toBe('Hello World');
});
