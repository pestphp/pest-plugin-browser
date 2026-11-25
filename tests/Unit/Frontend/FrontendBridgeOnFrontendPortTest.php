<?php

declare(strict_types=1);

use Pest\Browser\Exceptions\FrontendUnavailableException;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

beforeEach(function (): void {
    $this->port = Port::find();
});

afterEach(function (): void {
    $frontend = Frontend::instance();

    Frontend::reset();
    FrontendFixture::stopBridge($frontend->bridge());
    FrontendFixture::stopPort($this->port);
});

it('refuses a bridge on the port of the frontend', function (): void {
    pest()->browser()
        ->frontend(FrontendFixture::directory(), FrontendFixture::command(), $this->port)
        ->backendUrlEnv('FIXTURE_BACKEND_URL')
        ->bridgePort($this->port)
        ->timeout(5);

    expect(fn (): string => Frontend::instance()->url('/'))
        ->toThrow(FrontendUnavailableException::class, 'The browser bridge and the frontend both use port');
});
