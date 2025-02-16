<?php

declare(strict_types=1);

test('assert title', function (): void {
    $browser = $this->visit(htmlfixture('default'));

    $browser->assertTitle('Pest Browser Test');
});
