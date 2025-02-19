<?php

declare(strict_types=1);

it('double clicks an element using css selectors', function (): void {
    $this->visit('/')
        ->assertSee('Double click me')
        ->assertDontSee('Double clicked!')
        ->doubleClick("button[data-testId='double-click']")
        ->assertSee('Double clicked!')
        ->assertDontSee('Double click me');
});

it('escapes double quotes properly', function () {
    $this->visit('/')
        ->doubleClick('button[data-testId="double-click"]')
        ->assertSee('Double clicked!');
});

it('can double click multiple times', function (): void {
    $this->visit('/')
        ->doubleClick('button[data-testId="double-click"]')
        ->assertSee('Double clicked! (1)')
        ->doubleClick('button[data-testId="double-click"]')
        ->doubleClick('button[data-testId="double-click"]')
        ->assertSee('Double clicked! (3)');
});
