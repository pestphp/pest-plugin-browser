<?php

declare(strict_types=1);

test('assert checkbox is checked', function () {
    $url = 'http://localhost:9357';

    $this->visit($url)
        ->assertChecked('input[name="checkbox-checked"]');
});
