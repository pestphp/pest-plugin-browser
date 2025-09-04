<?php

declare(strict_types=1);

use Pest\Browser\ServerManager;
use Illuminate\Support\Facades\Route;

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

it('changes the hostname for all requests', function (): void {
    Route::domain('pest.test')->group(function (): void {
        Route::get('/about', fn (): string => 'Hello Pest');
    });

    pest()->browser()->withHostname('pest.test');

    visit('/about')->assertSee('Hello Pest');
});
