<?php

declare(strict_types=1);

test('assert does not see', function () {
    $this->visit('/')
        ->assertDontSee('The PHP Framework For Artisans');
});

test('assert does not see ignoring case', function () {
    $this->visit('/')
        ->assertDontSee('the php framework for artisans', true);
});
