<?php

declare(strict_types=1);

describe('refresh', function () {
    it('refreshes the page', function (): void {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertUrlIs(playgroundUrl('/test/interactive-elements'))
            ->refresh()
            ->assertUrlIs(playgroundUrl('/test/interactive-elements'));
    });
});
