<?php

declare(strict_types=1);

use Pest\Browser\ServerManager;

it('rewrites the URLs on JS files', function (): void {
    @file_put_contents(
        public_path('app.js'),
        <<<'JS'
        console.log('Hello http://localhost');
        JS,
    );

    $server = ServerManager::instance()->http();
    $page = visit('/app.js');

    $page->assertSee("http://{$server->host}:{$server->port}")
        ->assertDontSee('http://localhost');
});
