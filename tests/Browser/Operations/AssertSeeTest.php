<?php

declare(strict_types=1);

test('assert sees', function () {
    $this->visit(htmlfixture('default'))
        ->assertSee('Test Page');
});

test('assert sees ignoring case', function () {
    $this->visit(htmlfixture('default'))
        ->assertSee('test page', true);
});
