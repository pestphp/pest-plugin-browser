<?php

declare(strict_types=1);

it('sees', function () {
    $this->visit(playground()->url())
        ->assertSee('Pest Plugin Browser');
});

it('sees ignoring case', function () {
    $this->visit(playground()->url())
        ->assertSee('pest plugin browser', true);
});

it('sees with special characters', function () {
    $this->visit(playground()->url('/test/interactive-elements'))
        ->assertSee('Some (text) wi/th [some] "formatted" ch@racters.');
});

it('sees without supporting regex', function () {
    $this->visit(playground()->url('/test/interactive-elements'))
        ->assertSee('text(.*)formatted');
})->throws(Exception::class);
