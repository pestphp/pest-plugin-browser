<?php

declare(strict_types=1);

describe('doubleClick', function () {
    it('double clicks an element', function (): void {
        $this->visit(playgroundUrl('/test/interacting-with-elements'))
            ->assertSee('Double click me')
            ->assertDontSee('Double clicked! (1)')
            ->doubleClick('button[data-testid="double-click"]')
            ->pause(100)
            ->assertSee('Double clicked! (1)')
            ->assertDontSee('Double click me');
    });

    it('escapes double quotes properly', function () {
        $this->visit(playgroundUrl('/test/interacting-with-elements'))
            ->doubleClick('button[data-testId="double-click"]')
            ->pause(100)
            ->assertSee('Double clicked!');
    });

    it('can double click multiple times', function (): void {
        $this->visit(playgroundUrl('/test/interacting-with-elements'))
            ->doubleClick('button[data-testId="double-click"]')
            ->pause(100)
            ->assertSee('Double clicked! (1)')
            ->doubleClick('button[data-testId="double-click"]')
            ->doubleClick('button[data-testId="double-click"]')
            ->pause(100)
            ->assertSee('Double clicked! (3)');
    });
});
