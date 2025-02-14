<?php

declare(strict_types=1);

namespace Pest\Browser;

function visit(string $url): PendingTest
{
    return (new PendingTest)->visit($url);
}
