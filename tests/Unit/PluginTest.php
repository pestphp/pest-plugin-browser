<?php

declare(strict_types=1);

use Pest\Browser\Exceptions\HttpServerConfigurationException;
use Pest\Browser\Plugin;

it('throws an exception when an invalid port is provided', function (): void {
    $plugin = new Plugin;
    $plugin->handleArguments(['--playwright-port=abc']);
})->throws(HttpServerConfigurationException::class);
