<?php

declare(strict_types=1);

test('assert does not see', function () {
    $this->visit(playgroundUrl('/'))
        ->assertSee('Pest Plugin Browser')
        ->assertDontSee('Fest Plugin Bowser');
});

test('assert does not see ignoring case', function () {
    $this->visit(playgroundUrl('/'))
        ->assertSee('Pest Plugin Browser')
        ->assertDontSee('fest plugin bowser', true);
});

test('assert does not see escaping regex special characters', function () {
    $this->visit(playgroundUrl('/test/interactive-elements'))
        ->assertSee('Some (text) with some "formatted" characters.')
        ->assertDontSee('Some (text) which "does" not exist.');
});
