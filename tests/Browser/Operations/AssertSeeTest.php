<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertSee', function () {
    it('passes when given text is visible', function (string $path, string $text) {
        $this->visit(playgroundUrl($path))
            ->assertSee($text);
    })->with([
        ['/', 'Pest Plugin Browser'],
        ['/', 'pest plugin browser'],
        ['/test/interactive-elements', 'Some (text) wi/th [some] "formatted" ch@racters.'],
    ]);

    it('fails when given text is not visible', function () {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertSee('text(.*)formatted');
    })->throws(ExpectationFailedException::class);
});
