<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertDontSee', function () {
    it('passes when given text is not visible', function (string $path, string $text) {
        $this->visit(playgroundUrl($path))
            ->assertDontSee($text);
    })->with([
        ['/', 'Fest Plugin Bowser'],
        ['/', 'fest plugin bowser'],
        ['/test/interactive-elements', 'Some (text) which "does" not exist.'],
    ]);

    it('fails when given text is visible', function () {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertSee('text(.*)formatted');
    })->throws(ExpectationFailedException::class);
});
