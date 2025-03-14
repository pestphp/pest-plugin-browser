<?php

declare(strict_types=1);

test('assert title contains', function () {
    $this->visit(playground()->url())
        ->assertTitleContains('Plugin Browser');
});
