<?php

declare(strict_types=1);

use Symfony\Component\Process\Exception\ProcessFailedException;

it('setTimeout', function (): void {
    $this->visit('/test/interactive-elements')
        ->assertDontSee('I appear after 2 seconds')
        ->setTimeout(12000)
        ->pause(2200)
        ->assertSee('I appear after 2 seconds');
});

test('throws an exception when setTimeout is less than pause', function (): void {
    expect(function () {
        $this->visit('/test/interactive-elements')
            ->setTimeout(5000)
            ->pause(7000);
    })->toThrow(
        ProcessFailedException::class,
        'Test timeout of 5000ms exceeded.'
    );
});

test('throws an exception when setTimeout is negative', function (): void {
    expect(function () {
        $this->visit('/')
            ->clickLink('Get Started')
            ->setTimeout(-1000)
            ->assertUrlIs(playgroundUrl());
    })
        ->toThrow(
            InvalidArgumentException::class,
            'The number of milliseconds must be greater than 0.'
        );
});
