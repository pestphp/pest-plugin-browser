<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

it('can find elements by label within container', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $containerLocator = $page->locator('body');
    $container = $containerLocator->elementHandle();

    $element = $container->getByLabel('Username');

    expect($element)->toBeInstanceOf(Element::class);
    expect($element->getAttribute('value'))->toBe('johndoe');
});
