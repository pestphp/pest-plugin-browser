<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

/**
 * @internal
 */
final class Playwright
{
    /**
     * Browser types
     *
     * @var array<string, BrowserType>
     */
    private static array $browserTypes = [];

    /**
     * Playwright server
     */
    private static ?Server $server = null;

    /**
     * Playwright client
     */
    private static ?Client $client = null;

    /**
     * Get chromium browser type
     */
    public static function chromium(): BrowserType
    {
        return self::$browserTypes['chromium'] ?? self::initialize('chromium');
    }

    /**
     * Get firefox browser type
     */
    public static function firefox(): BrowserType
    {
        return self::$browserTypes['firefox'] ?? self::initialize('firefox');
    }

    /**
     * Get webkit browser type
     */
    public static function webkit(): BrowserType
    {
        return self::$browserTypes['webkit'] ?? self::initialize('webkit');
    }

    /**
     * Initialize Playwright
     */
    private static function initialize(string $browser): BrowserType
    {
        self::$server = Server::start();
        self::$client = Client::listen(self::$server->url()."?browser={$browser}");

        $response = self::$client->execute('', 'initialize', ['sdkLanguage' => 'javascript']);

        foreach ($response as $message) {
            if (
                isset($message['method'])
                && $message['method'] === '__create__'
                && isset($message['params']['type'])
                && $message['params']['type'] === 'BrowserType'
            ) {
                $name = $message['params']['initializer']['name'] ?? '';
                self::$browserTypes[$name] = new BrowserType($message['params']['guid'], $name);
            }
        }

        return self::$browserTypes[$browser];
    }
}
