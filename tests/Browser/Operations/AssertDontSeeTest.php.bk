<?php

declare(strict_types=1);

it('does not see', function () {
    $this->visit(playgroundUrl('/'))
        ->assertSee('Pest Plugin Browser')
        ->assertDontSee('Fest Plugin Bowser');
});

it('does not see ignoring case', function () {
    $this->visit(playgroundUrl('/'))
        ->assertSee('Pest Plugin Browser')
        ->assertDontSee('fest plugin bowser', true);
});

it('does not see with special characters', function () {
    $this->visit(playgroundUrl('/test/interactive-elements'))
        ->assertSee('Some (text) wi/th [some] "formatted" ch@racters.')
        ->assertDontSee('Some (text) which "does" not exist.');
});

it('does not see while supporting regex', function () {
    $this->visit(playgroundUrl('/test/interactive-elements'))
        ->assertSee('text(.*)formatted');
})->throws(Exception::class);
