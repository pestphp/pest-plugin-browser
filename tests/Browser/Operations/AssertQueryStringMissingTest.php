<?php

declare(strict_types=1);

test('assert query string does not have the given query param with value', function () {
    $this->visit(playground()->url('/?q=test'))
        ->assertQueryStringHas('q')
        ->assertQueryStringHas('q', 'test')
        ->assertQueryStringMissing('q', 'test-1');
});

test('assert query string does not have the only given the query param', function () {
    $this->visit(playground()->url('/?q=test'))
        ->assertQueryStringMissing('s');
});
