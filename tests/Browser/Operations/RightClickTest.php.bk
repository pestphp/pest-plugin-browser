<?php

declare(strict_types=1);

it('right clicks an element using css selectors', function (): void {
    $this->visit('/test/interacting-with-elements')
        ->assertSee('Right click me')
        ->assertDontSee('Right clicked!')
        ->rightClick("button[data-testId='right-click']")
        ->assertSee('Right clicked!')
        ->assertDontSee('Right click me');
});

it('escapes double quotes properly', function () {
    $this->visit('/test/interacting-with-elements')
        ->rightClick('button[data-testId="right-click"]')
        ->assertSee('Right clicked!');
});

it('can right click multiple times', function (): void {
    $this->visit('/test/interacting-with-elements')
        ->rightClick('button[data-testId="right-click"]')
        ->assertSee('Right clicked! (1)')
        ->rightClick('button[data-testId="right-click"]')
        ->rightClick('button[data-testId="right-click"]')
        ->assertSee('Right clicked! (3)');
});
