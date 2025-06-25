<?php

declare(strict_types=1);

it('rewrites the URLs on JS files', function (): void {
    $page = visit('app.js');

    $contents = $page->assertSee('http://127.0.0.1')
        ->assertDontSee('https://my-app.test');
});
