<?php

declare(strict_types=1);

test('assert url is', function (): void {

    $faked = htmlfixture('default');

    $browser = $this->visit($faked);

    $browser->assertUrlIs($faked);
});
