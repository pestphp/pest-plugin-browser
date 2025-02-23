<?php

declare(strict_types=1);

test('assert host is', function () {
    $url = parse_url(playground()->url());

    $this->visit(playground()->url())
        ->assertHostIs("{$url['host']}:{$url['port']}");
});
