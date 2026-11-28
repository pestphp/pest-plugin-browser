<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

/**
 * Holds console logs and JavaScript errors reported by the browser.
 *
 * The state lives outside the page: `window.name` is application-owned and a
 * single `console.log()` can replace it, and origin-scoped storage does not
 * survive a cross-origin navigation in the same tab. Entries are keyed by
 * frame guid, which stays stable across such navigations.
 *
 * @internal
 */
final class BrowserLogCollector
{
    /**
     * @var array<string, array<int, array{message: string, timestamp?: int}>>
     */
    private static array $consoleLogs = [];

    /**
     * @var array<string, array<int, array{message: string, filename?: string, lineno?: int, colno?: int}>>
     */
    private static array $jsErrors = [];

    /**
     * @param  array<string, mixed>  $entry
     */
    public static function add(string $frameGuid, array $entry): void
    {
        $kind = $entry['kind'] ?? null;

        unset($entry['kind']);

        /** @var array{message: string} $entry */
        if ($kind === 'log') {
            self::$consoleLogs[$frameGuid][] = $entry;
        }

        if ($kind === 'error') {
            self::$jsErrors[$frameGuid][] = $entry;
        }
    }

    /**
     * @return array<int, array{message: string, timestamp?: int}>
     */
    public static function consoleLogs(string $frameGuid): array
    {
        return self::$consoleLogs[$frameGuid] ?? [];
    }

    /**
     * @return array<int, array{message: string, filename?: string, lineno?: int, colno?: int}>
     */
    public static function javaScriptErrors(string $frameGuid): array
    {
        return self::$jsErrors[$frameGuid] ?? [];
    }

    public static function flush(): void
    {
        self::$consoleLogs = [];
        self::$jsErrors = [];
    }
}
