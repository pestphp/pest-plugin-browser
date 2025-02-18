<?php

declare(strict_types=1);

test('assert checkbox is not checked', function () {
    $url = 'http://localhost:9357';

    $this->visit($url)
        ->assertNotChecked('input[name="checkbox-unchecked"]');
});
