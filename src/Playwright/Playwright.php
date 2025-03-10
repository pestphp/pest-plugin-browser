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
        $response = Client::instance()->execute(
            '',
            'initialize',
            ['sdkLanguage' => 'javascript']
        );

        /** @var array{method: string|null, params: array{type: string|null, guid: string, initializer: array{name: string|null}}} $message */
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
