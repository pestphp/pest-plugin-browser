<?php

declare(strict_types=1);

it('clicks an element using css selectors', function (): void {
    $this->visit('/')
        ->assertSee('Click me')
        ->assertDontSee('Single clicked!')
        ->doubleClick("button[data-testId='default-click']")
        ->assertSee('Single clicked!')
        ->assertDontSee('Click me');
});

it('escapes double quotes properly', function () {
    $this->visit('/')
        ->click('header a[title="Documentation"]:visible')
        ->assertUrlIs('https://pestphp.com/docs/installation');
});

it('can click multiple times', function (): void {
    $this->visit('/')
        ->click('button[data-testId="default-click"]')
        ->assertSee('Single clicked! \(1\)')
        ->click('button[data-testId="default-click"]')
        ->click('button[data-testId="default-click"]')
        ->assertSee('Single clicked! \(3\)');
});
