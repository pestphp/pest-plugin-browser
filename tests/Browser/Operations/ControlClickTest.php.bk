<?php

declare(strict_types=1);

it('control clicks an element using css selectors', function (): void {
    $this->visit('/test/interacting-with-elements')
        ->assertSee('ctrl|cmd + click me')
        ->assertDontSee('ctrl|cmd clicked!')
        ->controlClick("button[data-testId='control-click']")
        ->assertSee('ctrl|cmd clicked!')
        ->assertDontSee('ctrl|cmd + click me');
});

it('escapes control quotes properly', function () {
    $this->visit('/test/interacting-with-elements')
        ->controlClick('button[data-testId="control-click"]')
        ->assertSee('ctrl|cmd clicked!');
});

it('can control click multiple times', function (): void {
    $this->visit('/test/interacting-with-elements')
        ->controlClick('button[data-testId="control-click"]')
        ->assertSee('ctrl|cmd clicked! (1)')
        ->controlClick('button[data-testId="control-click"]')
        ->controlClick('button[data-testId="control-click"]')
        ->assertSee('ctrl|cmd clicked! (3)');
});
