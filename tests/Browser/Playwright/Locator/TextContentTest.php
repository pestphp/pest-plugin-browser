<?php

declare(strict_types=1);

it('can get text content', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $element = $page->getByTestId('profile-section');

    expect($element->textContent())->toContain('Profile Section');
});
