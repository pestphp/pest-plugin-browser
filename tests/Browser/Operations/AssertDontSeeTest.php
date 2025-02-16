<?php

declare(strict_types=1);

test('assert does not see', function () {
    $this->visit(htmlFixture('default'))
        ->assertDontSee('The PHP Framework For Artisans test');
});

test('assert does not see ignoring case', function () {
    $this->visit(htmlFixture('default'))
        ->assertDontSee('the php framework for artisans test', true);
});
