<?php

declare(strict_types=1);

it('can get bounding box of element', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $button = $page->getByTestId('click-button');

    $boundingBox = $button->boundingBox();

    expect($boundingBox)->toBeArray();
    expect($boundingBox)->toHaveKeys(['x', 'y', 'width', 'height']);
    expect($boundingBox['x'])->toBeFloat();
    expect($boundingBox['y'])->toBeFloat();
    expect($boundingBox['width'])->toBeFloat();
    expect($boundingBox['height'])->toBeFloat();
    expect($boundingBox['width'])->toBeGreaterThan(0);
    expect($boundingBox['height'])->toBeGreaterThan(0);
});

it('returns null for hidden elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $hiddenElement = $page->getByTestId('hidden-element');

    $boundingBox = $hiddenElement->boundingBox();

    expect($boundingBox)->toBeNull();
});

it('bounding box coordinates are valid', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $button = $page->getByTestId('click-button');

    $boundingBox = $button->boundingBox();

    expect($boundingBox['x'])->toBeGreaterThanOrEqual(0);
    expect($boundingBox['y'])->toBeGreaterThanOrEqual(0);
    expect($boundingBox['width'])->toBeGreaterThan(0);
    expect($boundingBox['height'])->toBeGreaterThan(0);
});
