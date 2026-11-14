<?php

declare(strict_types=1);

namespace Tests\Support;

use Closure;
use Pest\Browser\Support\Port;
use RuntimeException;
use Symfony\Component\Process\Process;

final class ExternalSite
{
    /**
     * Run a test against a separate HTTP origin without accessing the internet.
     *
     * @param  Closure(string): void  $callback
     */
    public static function run(Closure $callback): void
    {
        $address = '127.0.0.1:'.Port::find();
        $process = new Process([PHP_BINARY, '-S', $address, '-t', __DIR__.'/../Fixtures/external-site']);
        $process->setTimeout(10);

        try {
            $process->start();

            if (! $process->waitUntil(fn (string $type, string $output): bool => str_contains($output, 'Development Server'))) {
                throw new RuntimeException('Could not start the external fixture server: '.$process->getErrorOutput());
            }

            $callback('http://'.$address);
        } finally {
            $process->stop();
        }
    }
}
