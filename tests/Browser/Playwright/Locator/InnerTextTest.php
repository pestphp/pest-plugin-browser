<?php

declare(strict_types=1);

it('can get inner text', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $element = $page->getByTestId('profile-section');

    expect($element->innerText())->toContain('Profile Section');
});
