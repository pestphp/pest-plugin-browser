<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Drivers\LaravelHttpServer;
use Pest\Browser\Frontend\Network;
use Pest\Browser\ServerManager;

it('builds the links of a request of the frontend on the default port when its host names no port', function (string $host, string $link): void {
    Route::get('/profile', fn (): string => 'profile')->name('profile');
    Route::get('/link', fn (): string => route('profile'));

    app('router')->getRoutes()->refreshNameLookups();

    $http = ServerManager::instance()->http();
    $http->bootstrap();

    $server = (string) config('app.url');

    $response = Network::get($server.'/link', 5, ['host' => $host, LaravelHttpServer::BRIDGE_HEADER => '1']);

    expect($response['body'] ?? null)->toBe($link);
})->with([
    ['acme-w4123.localhost', 'http://acme-w4123.localhost/profile'],
    ['acme-w4123.localhost:80', 'http://acme-w4123.localhost/profile'],
    ['acme-w4123.localhost:3101', 'http://acme-w4123.localhost:3101/profile'],
]);
