<?php

declare(strict_types=1);

it('pause', function (): void {
    $this->visit('/test/interactive-elements')
        ->assertDontSee('I appear after 2 seconds')
        ->pause(2200)
        ->assertSee('I appear after 2 seconds');
});

test('throws an exception when pause is negative', function (): void {
    expect(function () {
        $this->visit('/')
            ->clickLink('Get Started')
            ->pause(-1000)
            ->assertUrlIs(playgroundUrl());
    })
        ->toThrow(
            InvalidArgumentException::class,
            'The number of milliseconds must be greater than 0.'
        );
});
