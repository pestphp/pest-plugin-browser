<?php

declare(strict_types=1);

it('clicks and hold an element using css selectors', function (): void {
    $this->visit('/test/interacting-with-elements')
        ->assertSee('Click and hold me')
        ->assertDontSee('Free hug!')
        ->clickAndHold("button[data-testId='hold-click']")
        ->assertSee('Free hug!')
        ->assertDontSee('Click and hold me');
});

it('escapes double quotes properly', function () {
    $this->visit('/test/interacting-with-elements')
        ->clickAndHold('button[data-testId="hold-click"]')
        ->assertSee('Free hug!');
});

it('can click and hold multiple times', function (): void {
    $this->visit('/test/interacting-with-elements')
        ->clickAndHold('button[data-testId="hold-click"]')
        ->assertSee('Free hug! \(1\)')
        ->clickAndHold('button[data-testId="hold-click"]')
        ->clickAndHold('button[data-testId="hold-click"]')
        ->assertSee('Free hug! \(3\)');
});

it('can click and hold for a given duration', function (): void {
    $this->visit('/test/interacting-with-elements')
        ->clickAndHold('button[data-testId="hold-click"]', 500)
        ->assertDontSee('Free hug!')
        ->clickAndHold('button[data-testId="hold-click"]', 1500)
        ->assertSee('Free hug!');
});
