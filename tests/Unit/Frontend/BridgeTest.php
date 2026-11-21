<?php

declare(strict_types=1);

use Pest\Browser\Exceptions\FrontendUnavailableException;
use Pest\Browser\Frontend\Bridge;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Frontend\Network;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

beforeEach(function (): void {
    $this->bridge = new Bridge(Port::find(), Frontend::DEFAULT_ROUTE_HEADERS, false);
});

afterEach(function (): void {
    FrontendFixture::stopBridge($this->bridge);
    FrontendFixture::stopBackends();
});

/**
 * @param  array<string, string>  $headers
 * @return array{status: int, body: string}
 */
function throughBridge(Bridge $bridge, string $path, array $headers = []): array
{
    $context = stream_context_create(['http' => [
        'header' => implode("\r\n", array_map(fn (string $name, string $value): string => "{$name}: {$value}", array_keys($headers), $headers)),
        'ignore_errors' => true,
    ]]);

    $body = (string) file_get_contents($bridge->url().$path, false, $context);

    return ['status' => (int) explode(' ', (string) http_get_last_response_headers()[0])[1], 'body' => $body];
}

it('answers with its identity', function (): void {
    $this->bridge->ensureRunning();

    expect($this->bridge->identity())
        ->signature->toBe($this->bridge->signature())
        ->pid->toBeGreaterThan(0)
        ->and(json_decode(throughBridge($this->bridge, '/api/v1'.Bridge::IDENTITY_PATH)['body'], true))
        ->toMatchArray(['bridge' => 'pest', 'port' => $this->bridge->port]);
});

it('reuses the bridge that already listens', function (): void {
    $this->bridge->ensureRunning();
    $pid = $this->bridge->identity()['pid'];

    $this->bridge->ensureRunning();

    expect($this->bridge->identity()['pid'])->toBe($pid);
});

it('restarts an idle bridge that routes with another configuration', function (): void {
    $this->bridge->ensureRunning();
    $pid = $this->bridge->identity()['pid'];

    $this->bridge = new Bridge($this->bridge->port, ['x-tenant-domain'], true);
    $this->bridge->ensureRunning();

    expect($this->bridge->identity())
        ->pid->not->toBe($pid)
        ->signature->toBe($this->bridge->signature());
});

it('refuses a busy bridge that routes with another configuration', function (): void {
    $this->bridge->ensureRunning();
    $this->bridge->register(getmypid(), FrontendFixture::backend());

    $other = new Bridge($this->bridge->port, ['x-tenant-domain'], true);

    expect(fn () => $other->ensureRunning())->toThrow(FrontendUnavailableException::class, 'routes with another configuration');
});

it('refuses a port that another program uses', function (): void {
    $backend = FrontendFixture::backend();
    $bridge = new Bridge((int) parse_url($backend, PHP_URL_PORT), Frontend::DEFAULT_ROUTE_HEADERS, false);

    expect(fn () => $bridge->ensureRunning())->toThrow(FrontendUnavailableException::class, 'is used by another program');
});

it('routes to the lane named in the host of a route header', function (): void {
    $this->bridge->ensureRunning();

    $other = FrontendFixture::backend();
    $this->bridge->register(getmypid(), FrontendFixture::backend());
    $this->bridge->register(FrontendFixture::pidOf($other), $other);

    $response = throughBridge($this->bridge, '/users?page=2', ['X-Forwarded-Host' => 'pest-w'.FrontendFixture::pidOf($other).'.localhost:3101']);

    expect($response['status'])->toBe(200)
        ->and(json_decode($response['body'], true))->toBe([
            'path' => '/users?page=2',
            'host' => mb_substr($other, 7),
            'bridge' => '1',
        ]);
});

it('routes to the lane named in the referer', function (): void {
    $this->bridge->ensureRunning();

    $own = FrontendFixture::backend();
    $other = FrontendFixture::backend();
    $this->bridge->register(getmypid(), $own);
    $this->bridge->register(FrontendFixture::pidOf($other), $other);

    $response = throughBridge($this->bridge, '/users', ['Referer' => 'http://acme-w'.getmypid().'.localhost:3101/dashboard']);

    expect(json_decode($response['body'], true)['host'])->toBe(mb_substr($own, 7));
});

it('routes to the lane of an explicit lane header', function (): void {
    $this->bridge->ensureRunning();

    $own = FrontendFixture::backend();
    $other = FrontendFixture::backend();
    $this->bridge->register(getmypid(), $own);
    $this->bridge->register(FrontendFixture::pidOf($other), $other);

    $response = throughBridge($this->bridge, '/users', ['X-Pest-Lane' => (string) getmypid()]);

    expect(json_decode($response['body'], true)['host'])->toBe(mb_substr($own, 7));
});

it('routes a request without a lane to the only running process', function (): void {
    $this->bridge->ensureRunning();

    $own = FrontendFixture::backend();
    $this->bridge->register(getmypid(), $own);
    $this->bridge->register(999_999_999, FrontendFixture::backend());

    $response = throughBridge($this->bridge, '/users');

    expect(json_decode($response['body'], true)['host'])->toBe(mb_substr($own, 7));
});

it('refuses a request without a lane when several processes run', function (): void {
    $this->bridge->ensureRunning();

    $other = FrontendFixture::backend();
    $this->bridge->register(getmypid(), FrontendFixture::backend());
    $this->bridge->register(FrontendFixture::pidOf($other), $other);

    $response = throughBridge($this->bridge, '/users');

    expect($response['status'])->toBe(502)
        ->and($response['body'])->toContain('cannot tell which Pest process owns GET /users');
});

it('forwards the host of the lane when configured', function (): void {
    $this->bridge = new Bridge($this->bridge->port, ['x-tenant-domain'], true);
    $this->bridge->ensureRunning();
    $this->bridge->register(getmypid(), FrontendFixture::backend());

    $response = throughBridge($this->bridge, '/users', ['X-Tenant-Domain' => 'acme-w'.getmypid().'.localhost:3101']);

    expect(json_decode($response['body'], true)['host'])->toBe('acme-w'.getmypid().'.localhost:3101');
});

it('answers a lane without a running server with a bad gateway', function (): void {
    $this->bridge->ensureRunning();
    $this->bridge->register(getmypid(), 'http://127.0.0.1:'.Port::find());

    expect(throughBridge($this->bridge, '/users')['status'])->toBe(502);
});

it('forgets the lane of a process', function (): void {
    $this->bridge->register(getmypid(), 'http://127.0.0.1:8000');
    $this->bridge->forget(getmypid());

    expect(file_exists($this->bridge->lanesDirectory().'/'.getmypid()))->toBeFalse()
        ->and(Network::isListening($this->bridge->port))->toBeFalse();
});
