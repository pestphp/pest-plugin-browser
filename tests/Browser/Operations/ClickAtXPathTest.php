<?php

declare(strict_types=1);

it('clicks an element using xpath', function (): void {
    $this->visit(playground()->url('/test/interacting-with-elements'))
        ->clickAtXPath('/html/body/main/nav[1]/ul/li[1]/a')
        ->assertUrlIs(playground()->url());
});
