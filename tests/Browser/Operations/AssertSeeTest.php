<?php

declare(strict_types=1);

test('assert sees', function () {
    $this->visit(playgroundUrl('/'))
        ->assertSee('Pest Plugin Browser');
});

test('assert sees ignoring case', function () {
    $this->visit(playgroundUrl('/'))
        ->assertSee('pest plugin browser', true);
});

test('assert does not see escaping regex special characters', function () {
    $this->visit(playgroundUrl('/test/interactive-elements'))
        ->assertSee('Some (text) with some "formatted" characters.');
});
