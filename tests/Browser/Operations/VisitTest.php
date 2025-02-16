<?php

declare(strict_types=1);

test('visit', function (): void {
    $faked = htmlfixture('default');

    $browser = $this->visit($faked);

    $browser->assertUrlIs($faked);
});
