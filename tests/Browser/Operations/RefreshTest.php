<?php

declare(strict_types=1);

test('refreshes', function (): void {
    $this->visit(playground()->url('/test/interactive-elements'))
        ->assertUrlIs(playground()->url('/test/interactive-elements'))
        ->pause(3000)
        ->assertSee('I appear after 2 seconds')
        ->refresh()
        ->assertUrlIs(playground()->url('/test/interactive-elements'))
        ->assertDontSee('I appear after 2 seconds');
});
