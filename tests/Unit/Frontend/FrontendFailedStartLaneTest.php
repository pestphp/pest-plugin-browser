<?php

declare(strict_types=1);

use Pest\Browser\Exceptions\FrontendUnavailableException;
use Pest\Browser\Frontend\Bridge;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

beforeEach(function (): void {
    $this->bridge = new Bridge(Port::find(), Frontend::DEFAULT_ROUTE_HEADERS, false, Frontend::project());
    $this->port = Port::find();
});

afterEach(function (): void {
    Frontend::reset();
    FrontendFixture::stopPort($this->port);
    FrontendFixture::stopBridge($this->bridge);
});

it('removes the lane of this process when the frontend cannot start', function (): void {
    pest()->browser()
        ->frontend('/does/not/exist', 'true', $this->port)
        ->bridgePort($this->bridge->port)
        ->timeout(2);

    expect(fn (): string => Frontend::instance()->url('/'))->toThrow(FrontendUnavailableException::class, 'does not exist');

    expect(Bridge::hasLiveLanesIn($this->bridge->lanesDirectory()))->toBeFalse();
});
