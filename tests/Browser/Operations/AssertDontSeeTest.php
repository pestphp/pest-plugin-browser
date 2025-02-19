<?php

declare(strict_types=1);

test('assert does not see', function () {
    $this->visit('/')
        ->assertDontSee('I do not exist on the page, or anywhere on the website!');
});
