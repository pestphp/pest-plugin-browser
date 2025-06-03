<?php

declare(strict_types=1);

it('can click on buttons', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $button = $page->getByTestId('click-button');
    $counter = $page->getByTestId('click-counter');

    expect($counter->textContent())->toBe('0');

    $button->click();

    expect($counter->textContent())->toBe('1');
});
