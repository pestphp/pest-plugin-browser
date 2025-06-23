<?php

declare(strict_types=1);

it('closes the page', function (): void {
    $page = page('/test/frame-tests');

    expect($page->url())->toContain('/test/frame-tests');

    $page->close();

    expect(true)->toBeTrue();
});

it('can be called multiple times without errors', function (): void {
    $page = page('/test/frame-tests');

    $page->close();
    $page->close();

    expect($page->isClosed())->toBeTrue();
});
