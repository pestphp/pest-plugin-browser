<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

beforeEach(function (): void {
    pest()->browser()
        ->frontend(FrontendFixture::directory(), FrontendFixture::command(), Port::find())
        ->backendUrlEnv('FIXTURE_BACKEND_URL')
        ->bridgePort(Port::find())
        ->timeout(10);
});

afterEach(function (): void {
    $frontend = Frontend::instance();

    Frontend::reset();
    FrontendFixture::stopPort($frontend->port);
    FrontendFixture::stopBridge($frontend->bridge());
});

it('does not tell a consumer of a scoped service that a request of the frontend failed to rebind', function (): void {
    app()->scoped('frontend-scoped-counter', fn (): stdClass => new stdClass);

    app('frontend-scoped-counter');

    $rebound = 0;

    app()->rebinding('frontend-scoped-counter', function () use (&$rebound): void {
        $rebound++;
    });

    Route::get('/client', function (): array {
        try {
            app()->bind('frontend-scoped-counter', fn (): never => throw new RuntimeException('unavailable'));
        } catch (RuntimeException) {
            return ['status' => 'failed'];
        }

        return ['status' => 'bound'];
    });

    visit(frontend('/api/client'))
        ->assertSee('"status":"failed"');

    expect($rebound)->toBe(0);
});

it('gives the service of the test back to a consumer when a request of the frontend rebinds it', function (): void {
    app()->scoped('frontend-scoped-counter', fn (): stdClass => new stdClass);

    $service = app('frontend-scoped-counter');

    $received = null;

    app()->rebinding('frontend-scoped-counter', function ($app, object $instance) use (&$received): void {
        $received = $instance;
    });

    Route::get('/client', function (): array {
        app()->bind('frontend-scoped-counter', fn (): stdClass => (object) ['frontend' => true]);

        return ['status' => 'bound'];
    });

    visit(frontend('/api/client'))
        ->assertSee('"status":"bound"');

    expect(app('frontend-scoped-counter'))->toBe($service)
        ->and($received)->toBe($service);
});

it('gives the service of the test back to a consumer the request of the frontend registers before it rebinds it', function (): void {
    app()->scoped('frontend-scoped-counter', fn (): stdClass => new stdClass);

    $service = app('frontend-scoped-counter');

    $consumer = new class
    {
        public ?object $service = null;

        public function setService(object $service): void
        {
            $this->service = $service;
        }
    };

    Route::get('/client', function () use ($consumer): array {
        app()->refresh('frontend-scoped-counter', $consumer, 'setService');

        app()->bind('frontend-scoped-counter', fn (): stdClass => (object) ['frontend' => true]);

        return ['status' => 'bound'];
    });

    visit(frontend('/api/client'))
        ->assertSee('"status":"bound"');

    expect($consumer->service)->toBe($service);
});

it('does not tell a consumer the request of the frontend registers when it fails to rebind the service', function (): void {
    app()->scoped('frontend-scoped-counter', fn (): stdClass => new stdClass);

    app('frontend-scoped-counter');

    $calls = 0;

    Route::get('/client', function () use (&$calls): array {
        app()->rebinding('frontend-scoped-counter', function () use (&$calls): void {
            $calls++;
        });

        try {
            app()->bind('frontend-scoped-counter', fn (): never => throw new RuntimeException('unavailable'));
        } catch (RuntimeException) {
            return ['status' => 'failed'];
        }

        return ['status' => 'bound'];
    });

    visit(frontend('/api/client'))
        ->assertSee('"status":"failed"');

    expect($calls)->toBe(0);
});

it('does not tell a consumer the request of the frontend only registers', function (): void {
    app()->scoped('frontend-scoped-counter', fn (): stdClass => new stdClass);

    app('frontend-scoped-counter');

    $calls = 0;

    Route::get('/client', function () use (&$calls): array {
        app()->rebinding('frontend-scoped-counter', function () use (&$calls): void {
            $calls++;
        });

        return ['status' => 'registered'];
    });

    visit(frontend('/api/client'))
        ->assertSee('"status":"registered"');

    expect($calls)->toBe(0);
});
