<?php

declare(strict_types=1);

test('refreshes', function (): void {
    $this->visit('/test/interactive-elements')
        ->assertUrlIs(playgroundUrl('/test/interactive-elements'))
        ->pause(3000)
        ->assertSee('I appear after 2 seconds')
        ->refresh()
        ->assertUrlIs(playgroundUrl('/test/interactive-elements'))
        ->assertDontSee('I appear after 2 seconds');
});
