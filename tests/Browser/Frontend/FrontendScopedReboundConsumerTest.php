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

it('gives the substitute of the test back to a consumer that refreshes it when a request of the frontend replaces it', function (): void {
    app()->scoped('frontend-scoped-mode', fn (): string => 'real');

    app()->instance('frontend-scoped-mode', 'fake');

    $consumer = new class
    {
        public ?string $mode = null;

        public function setMode(string $mode): void
        {
            $this->mode = $mode;
        }
    };

    $consumer->setMode(app()->refresh('frontend-scoped-mode', $consumer, 'setMode'));

    Route::get('/client', function (): array {
        app()->instance('frontend-scoped-mode', 'frontend');

        return ['mode' => app('frontend-scoped-mode')];
    });

    visit(frontend('/api/client'))
        ->assertSee('"mode":"frontend"');

    expect(app('frontend-scoped-mode'))->toBe('fake')
        ->and($consumer->mode)->toBe('fake');
});

it('gives the substitute of the test back to a consumer when a request of the frontend replaces it and then forgets it', function (): void {
    app()->scoped('frontend-scoped-mode', fn (): string => 'real');

    app()->instance('frontend-scoped-mode', 'fake');

    $consumer = new class
    {
        public ?string $mode = null;

        public function setMode(string $mode): void
        {
            $this->mode = $mode;
        }
    };

    $consumer->setMode(app()->refresh('frontend-scoped-mode', $consumer, 'setMode'));

    Route::get('/client', function (): array {
        app()->instance('frontend-scoped-mode', 'frontend');
        app()->forgetInstance('frontend-scoped-mode');

        return ['status' => 'ok'];
    });

    visit(frontend('/api/client'))
        ->assertSee('"status":"ok"');

    expect(app('frontend-scoped-mode'))->toBe('fake')
        ->and($consumer->mode)->toBe('fake');
});

it('does not tell a consumer of a scoped service that a request of the frontend only reads', function (): void {
    app()->scoped('frontend-scoped-counter', fn (): stdClass => new stdClass);

    app('frontend-scoped-counter');

    $rebound = 0;

    app()->rebinding('frontend-scoped-counter', function () use (&$rebound): void {
        $rebound++;
    });

    Route::get('/client', fn (): array => ['service' => app('frontend-scoped-counter')::class]);

    visit(frontend('/api/client'))
        ->assertSee('stdClass');

    expect($rebound)->toBe(0);
});

it('does not tell a consumer of a scoped service that the rebound of another service reads', function (): void {
    app()->scoped('frontend-scoped-counter', fn (): stdClass => new stdClass);

    app('frontend-scoped-counter');

    $rebound = 0;

    app()->rebinding('frontend-scoped-counter', function () use (&$rebound): void {
        $rebound++;
    });

    app()->rebinding('request', function (): void {
        app('frontend-scoped-counter');
    });

    Route::get('/client', fn (): array => ['status' => 'ok']);

    visit(frontend('/api/client'))
        ->assertSee('"status":"ok"');

    expect($rebound)->toBe(0);
});
