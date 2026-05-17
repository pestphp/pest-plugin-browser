## Summary

Allow connecting to an already-running Playwright server instead of starting a local one. This enables setups where Playwright runs on the host machine while Pest runs inside a container (e.g., Docker, CI).

## Problem

Currently `pest-plugin-browser` always starts a local Playwright server via `npx playwright run-server`. This fails when:

1. The PHP environment runs in an Alpine Linux container (musl libc) — Playwright's bundled Chromium requires glibc and crashes with `posix_fallocate64: symbol not found`
2. The host machine already has a running Playwright server that the container could connect to

There's no way to tell the plugin "don't start a server, connect to this existing one instead."

## Solution

Three small additions:

### 1. `Playwright::setPlaywrightServerUrl()` + getter

New static property `$playwrightServerUrl`. The getter checks both programmatic config and the `PEST_BROWSER_PLAYWRIGHT_URL` environment variable:

```php
public static function playwrightServerUrl(): ?string
{
    return self::$playwrightServerUrl ?? getenv('PEST_BROWSER_PLAYWRIGHT_URL') ?: null;
}
```

### 2. `Configuration::withPlaywrightServer(?string $serverUrl)`

New fluent config method, usable in `tests/Pest.php`:

```php
pest()->browser()->withPlaywrightServer('http://192.168.1.100:9999');
```

### 3. `ServerManager::playwright()` check

When a server URL is set, the plugin uses `AlreadyStartedPlaywrightServer` (existing class) to connect to the external server instead of calling `PlaywrightNpmServer::create()`.

## Usage

```php
// In tests/Pest.php
pest()->browser()
    ->withPlaywrightServer('http://192.168.1.100:9999')
    ->inChrome();
```

Or via environment variable:

```bash
PEST_BROWSER_PLAYWRIGHT_URL=http://192.168.1.100:9999 php vendor/bin/pest tests/Browser/
```

When not set, behavior is identical to before — fully backward compatible.
