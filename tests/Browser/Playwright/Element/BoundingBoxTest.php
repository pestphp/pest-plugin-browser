<?php

declare(strict_types=1);

it('can get bounding box of visible elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('profile-section');
    $element = $locator->elementHandle();

    $box = $element->boundingBox();

    expect($box)->toBeArray();
    expect($box)->toHaveKey('x');
    expect($box)->toHaveKey('y');
    expect($box)->toHaveKey('width');
    expect($box)->toHaveKey('height');
});

it('returns null for hidden elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('hidden-element');
    $element = $locator->elementHandle();

    $box = $element->boundingBox();

    expect($box)->toBeNull();
});
