<?php

declare(strict_types=1);

describe('pause', function () {
    it('makes a pause', function () {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertDontSee('I appear after 2 seconds')
            ->pause(2200)
            ->assertSee('I appear after 2 seconds');
    });

    test('throws an exception when pause is negative', function () {
        $this->visit(playgroundUrl())
            ->clickLink('Get Started')
            ->pause(-1000)
            ->assertUrlIs(playgroundUrl());
    })->throws(
        InvalidArgumentException::class,
        'Pause duration must be a non-negative value.'
    );
});
