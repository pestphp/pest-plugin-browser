<?php

declare(strict_types=1);

it('clicks a link', function (): void {
    $faked = htmlfixture('default');

    $this->visit($faked)
        ->clickLink('Click me')
        ->assertUrlIs($faked.'?query=boom');
});
