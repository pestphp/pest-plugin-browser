<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

it('can create a locator with CSS selector', function (): void {
    $page = page()->goto('/test/element-tests');
    $parentLocator = $page->getByTestId('profile-section');
    $childLocator = $parentLocator->locator('button');

    expect($childLocator)->toBeInstanceOf(Locator::class);
    expect($childLocator->isVisible())->toBeTrue();
});

it('can create nested locators using CSS selectors', function (): void {
    $page = page()->goto('/test/element-tests');
    $sectionLocator = $page->getByTestId('nested-container');
    $nestedLocator = $sectionLocator->locator('.parent .child');

    expect($nestedLocator)->toBeInstanceOf(Locator::class);
});

it('can chain multiple locator calls', function (): void {
    $page = page()->goto('/test/element-tests');
    $baseLocator = $page->getByTestId('profile-section');
    $firstLevel = $baseLocator->locator('div');
    $secondLevel = $firstLevel->locator('p');

    expect($baseLocator)->toBeInstanceOf(Locator::class);
    expect($firstLevel)->toBeInstanceOf(Locator::class);
    expect($secondLevel)->toBeInstanceOf(Locator::class);
});

it('preserves frame context when creating child locators', function (): void {
    $page = page()->goto('/test/element-tests');
    $parentLocator = $page->getByTestId('profile-section');
    $childLocator = $parentLocator->locator('button');

    expect($parentLocator->page())->toBe($childLocator->page());
});

it('can locate elements with complex selectors', function (): void {
    $page = page()->goto('/test/element-tests');
    $containerLocator = $page->getByTestId('nested-container');
    $complexLocator = $containerLocator->locator('div.parent > p.child:first-child');

    expect($complexLocator)->toBeInstanceOf(Locator::class);
});

it('can locate elements using attribute selectors', function (): void {
    $page = page()->goto('/test/element-tests');
    $sectionLocator = $page->getByTestId('profile-section');
    $buttonLocator = $sectionLocator->locator('button[type="button"]');

    expect($buttonLocator)->toBeInstanceOf(Locator::class);
});

it('can locate elements using pseudo-selectors', function (): void {
    $page = page()->goto('/test/element-tests');
    $containerLocator = $page->locator('.section');
    $firstSectionLocator = $containerLocator->locator(':first-child');

    expect($firstSectionLocator)->toBeInstanceOf(Locator::class);
});

it('can combine with other locator methods', function (): void {
    $page = page()->goto('/test/element-tests');
    $baseLocator = $page->getByTestId('profile-section');
    $textLocator = $baseLocator->locator('p')->getByText('This section has a data-testid attribute');

    expect($textLocator)->toBeInstanceOf(Locator::class);
    expect($textLocator->isVisible())->toBeTrue();
});

it('can interact with located elements', function (): void {
    $page = page()->goto('/test/element-tests');
    $sectionLocator = $page->getByTestId('profile-section');
    $buttonLocator = $sectionLocator->locator('button');

    expect($buttonLocator->isVisible())->toBeTrue();
    expect($buttonLocator->isEnabled())->toBeTrue();

    $buttonLocator->click();
    expect($buttonLocator)->toBeInstanceOf(Locator::class);
});

it('returns proper selector string format', function (): void {
    $page = page()->goto('/test/element-tests');
    $parentLocator = $page->getByTestId('profile-section');
    $childLocator = $parentLocator->locator('button');

    expect($childLocator->selector())->toContain(' >> ');
    expect($childLocator->selector())->toContain('button');
});

it('can locate form elements within containers', function (): void {
    $page = page()->goto('/test/element-tests');
    $formSectionLocator = $page->locator('.section');
    $inputLocator = $formSectionLocator->locator('input[type="text"]');

    expect($inputLocator)->toBeInstanceOf(Locator::class);
});

it('can locate specific element types', function (): void {
    $page = page()->goto('/test/element-tests');
    $containerLocator = $page->getByTestId('profile-section');

    $buttonLocator = $containerLocator->locator('button');
    $paragraphLocator = $containerLocator->locator('p');

    expect($buttonLocator)->toBeInstanceOf(Locator::class);
    expect($paragraphLocator)->toBeInstanceOf(Locator::class);
});

it('can use ID selectors within parent locator', function (): void {
    $page = page()->goto('/test/element-tests');
    $bodyLocator = $page->locator('body');
    $specificElementLocator = $bodyLocator->locator('#empty-id');

    expect($specificElementLocator)->toBeInstanceOf(Locator::class);
});

it('can use class selectors within parent locator', function (): void {
    $page = page()->goto('/test/element-tests');
    $containerLocator = $page->locator('body');
    $sectionLocator = $containerLocator->locator('.section');

    expect($sectionLocator)->toBeInstanceOf(Locator::class)
        ->and($sectionLocator->count())->toBeGreaterThan(0);
});

it('handles non-existent selectors gracefully', function (): void {
    $page = page()->goto('/test/element-tests');
    $containerLocator = $page->getByTestId('profile-section');
    $nonExistentLocator = $containerLocator->locator('.non-existent-class');

    expect($nonExistentLocator)->toBeInstanceOf(Locator::class);
    expect($nonExistentLocator->count())->toBe(0);
});

it('can work with multiple matching elements', function (): void {
    $page = page()->goto('/test/element-tests');
    $bodyLocator = $page->locator('body');
    $allButtonsLocator = $bodyLocator->locator('button');

    expect($allButtonsLocator)->toBeInstanceOf(Locator::class);
    expect($allButtonsLocator->count())->toBeGreaterThan(1);
});

it('can be used with nth selectors', function (): void {
    $page = page()->goto('/test/element-tests');
    $containerLocator = $page->locator('.section');
    $firstSectionLocator = $containerLocator->locator(':nth-child(1)');

    expect($firstSectionLocator)->toBeInstanceOf(Locator::class);
});

it('maintains selector hierarchy correctly', function (): void {
    $page = page()->goto('/test/element-tests');
    $level1 = $page->getByTestId('profile-section');
    $level2 = $level1->locator('div');
    $level3 = $level2->locator('p');

    expect($level1->selector())->toContain('[data-testid="profile-section"]');
    expect($level2->selector())->toContain('[data-testid="profile-section"]');
    expect($level2->selector())->toContain(' >> div');
    expect($level3->selector())->toContain('[data-testid="profile-section"]');
    expect($level3->selector())->toContain(' >> div >> p');
});

it('can locate elements using descendant selectors', function (): void {
    $page = page()->goto('/test/element-tests');
    $parentLocator = $page->getByTestId('nested-container');
    $descendantLocator = $parentLocator->locator('span');

    expect($descendantLocator)->toBeInstanceOf(Locator::class);
});

it('works with text content after locating', function (): void {
    $page = page()->goto('/test/element-tests');
    $sectionLocator = $page->getByTestId('profile-section');
    $textElementLocator = $sectionLocator->locator('p');

    expect($textElementLocator->textContent())->toContain('This section has a data-testid attribute');
});
