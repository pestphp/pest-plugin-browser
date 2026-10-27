<?php

declare(strict_types=1);

use Amp\Socket\ConnectContext;
use Amp\Socket\ConnectException;
use Amp\Socket\Socket;
use Tests\Support\ExternalSite;

use function Amp\Socket\connect;

it('stops the external fixture server even when the callback throws', function (bool $fail): void {
    $address = null;
    $caught = null;

    try {
        ExternalSite::run(function (string $url) use (&$address, $fail): void {
            $address = str_replace('http://', 'tcp://', $url);

            expect(file_get_contents($url))->toContain('External fixture page');

            if ($fail) {
                throw new RuntimeException('Fixture callback failed.');
            }
        });
    } catch (RuntimeException $exception) {
        $caught = $exception;
    }

    expect($caught?->getMessage())->toBe($fail ? 'Fixture callback failed.' : null);
    expect($address)->toBeString();
    expect(fn (): Socket => connect($address, new ConnectContext()->withConnectTimeout(0.1)))->toThrow(ConnectException::class);
})->with([false, true]);
