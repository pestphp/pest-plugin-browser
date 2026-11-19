<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

/**
 * @internal
 */
final class InitScript
{
    /**
     * Get the JavaScript code for the initialization script.
     */
    public static function get(): string
    {
        return <<<'JS_WRAP'
            // window.name persists across same-tab navigations even when the origin
            // changes (e.g. 127.0.0.1 -> localhost), unlike origin-scoped sessionStorage.
            // Namespace the payload so unrelated window.name usage is left untouched.
            const PEST_NAME_PREFIX = '__pestBrowser:';
            let storedErrors = [];
            let storedLogs = [];
            try {
                if (typeof window.name === 'string' && window.name.startsWith(PEST_NAME_PREFIX)) {
                    const stored = JSON.parse(window.name.slice(PEST_NAME_PREFIX.length));
                    if (Array.isArray(stored.jsErrors)) storedErrors = stored.jsErrors;
                    if (Array.isArray(stored.consoleLogs)) storedLogs = stored.consoleLogs;
                }
            } catch (_) {}

            window.__pestBrowser = {
                jsErrors: storedErrors,
                consoleLogs: storedLogs
            };

            function syncStorage() {
                try {
                    window.name = PEST_NAME_PREFIX + JSON.stringify({
                        jsErrors: window.__pestBrowser.jsErrors,
                        consoleLogs: window.__pestBrowser.consoleLogs
                    });
                } catch (_) {}
            }

            const originalConsoleLog = console.log;
            console.log = function(...args) {
                window.__pestBrowser.consoleLogs.push({
                    timestamp: new Date().getTime(),
                    message: args.map(arg => String(arg)).join(' ')
                });
                syncStorage();
                originalConsoleLog.apply(console, args);
            };

            window.addEventListener('error', (e) => {
                window.__pestBrowser.jsErrors.push({
                    message: e.message,
                    filename: e.filename,
                    lineno: e.lineno,
                    colno: e.colno
                });
                syncStorage();
            });

            window.addEventListener('unhandledrejection', (e) => {
                window.__pestBrowser.jsErrors.push({
                    message: e.reason instanceof Error ? e.reason.message : String(e.reason),
                    filename: '',
                    lineno: 0,
                    colno: 0
                });
                syncStorage();
            });
            JS_WRAP;
    }
}
