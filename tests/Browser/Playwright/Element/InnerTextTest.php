<?php

declare(strict_types=1);

it('can get inner text of elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByText('This is a simple paragraph', true);
    $element = $locator->elementHandle();

    expect(mb_trim((string) $element->innerText()))->toContain('This is a simple paragraph');
});
