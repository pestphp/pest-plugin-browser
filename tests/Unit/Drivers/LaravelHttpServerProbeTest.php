<?php

declare(strict_types=1);

use Pest\Browser\Drivers\LaravelHttpServer;

function mayBeProbe(string $laneHost, string $probing): bool
{
    $server = new ReflectionClass(LaravelHttpServer::class)->newInstanceWithoutConstructor();

    return new ReflectionMethod(LaravelHttpServer::class, 'mayBeProbe')->invoke($server, $laneHost, $probing);
}

it('takes a bridged request for the probe when the bridge routed it by no lane host', function (): void {
    expect(mayBeProbe('', 'acme-w4123.localhost:3102'))->toBeTrue();
});

it('takes a bridged request for the probe when it was routed by the probed lane host', function (string $laneHost): void {
    expect(mayBeProbe($laneHost, 'acme-w4123.localhost:3102'))->toBeTrue();
})->with(['acme-w4123.localhost:3102', 'ACME-w4123.localhost:3102', 'acme-w4123.localhost', 'ACME-w4123.localhost']);

it('takes the internationalized lane host the bridge writes in ASCII for the probe of that host', function (string $probing): void {
    expect(mayBeProbe('xn--caf-w4123-d4a.localhost', $probing))->toBeTrue()
        ->and(mayBeProbe('xn--caf-w4123-d4a.localhost:3102', $probing))->toBeTrue();
})->with(['caf%C3%A9-w4123.localhost:3102', 'café-w4123.localhost:3102'])->skip(! function_exists('idn_to_ascii'), 'Needs the intl functions.');

it('keeps the letters that only the transitional processing maps, as the bridge does', function (): void {
    expect(mayBeProbe('xn--strae-w1-tya.localhost', 'straße-w1.localhost:3102'))->toBeTrue()
        ->and(mayBeProbe('strasse-w1.localhost', 'straße-w1.localhost:3102'))->toBeFalse();
})->skip(! function_exists('idn_to_ascii'), 'Needs the intl functions.');

it('records the error of a page of another frontend routed by its own lane host', function (string $laneHost): void {
    expect(mayBeProbe($laneHost, 'acme-w4123.localhost:3102'))->toBeFalse();
})->with(['acme-w4123.localhost:3101', 'shop-w4123.localhost', 'shop-w4123.localhost:3102', 'xn--caf-w4123-d4a.localhost:3101']);

it('records the error of a page of a frontend on the same port of another address while a frontend starts', function (): void {
    expect(mayBeProbe('pest-w4123.localhost:3102', '[::1]:3102'))->toBeFalse();
});

it('converts a capital sigma before any case change, as the bridge does', function (): void {
    expect(mayBeProbe('xn--0xai.123-w4123.localhost', '%CE%9F%CE%A3.123-w4123.localhost:3102'))->toBeTrue();
})->skip(! function_exists('idn_to_ascii'), 'Needs the intl functions.');

it('ignores the hyphen and length checks the URL parser of the bridge leaves out', function (string $laneHost, string $probing): void {
    expect(mayBeProbe($laneHost, $probing))->toBeTrue();
})->with([
    // The lane hosts are what node's `new URL('http://'.$probing).host` gives, as the bridge reads them.
    ['xn--ab--caf-hya.shop-w4123.localhost:3102', 'ab--caf%C3%A9.shop-w4123.localhost:3102'],
    ['xn---caf-w1-eya.localhost:3102', '-café-w1.localhost:3102'],
    ['xn--caf'.str_repeat('a', 60).'-w4123-d9f.localhost:3102', 'café'.str_repeat('a', 60).'-w4123.localhost:3102'],
    ['xn--caf-dma..x-w4123.localhost:3102', 'café..x-w4123.localhost:3102'],
    ['xn--caf-dma.x-w4123.localhost:3102', 'café。x-w4123.localhost:3102'],
    ['.x-w4123.localhost:3102', '%C2%AD.x-w4123.localhost:3102'],
    [
        'xn--caf-dma.'.implode('.', array_fill(0, 4, str_repeat('a', 60))).'.shop-w4123.localhost:3102',
        'caf%C3%A9.'.implode('.', array_fill(0, 4, str_repeat('a', 60))).'.shop-w4123.localhost:3102',
    ],
])->skip(! function_exists('idn_to_ascii'), 'Needs the intl functions.');
