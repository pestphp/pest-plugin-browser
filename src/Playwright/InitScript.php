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
        return <<<'JS'
            let storedErrors = [];
            let storedLogs = [];
            try {
                const rawErrors = sessionStorage.getItem('__pestBrowser_jsErrors');
                if (rawErrors) storedErrors = JSON.parse(rawErrors);
                const rawLogs = sessionStorage.getItem('__pestBrowser_consoleLogs');
                if (rawLogs) storedLogs = JSON.parse(rawLogs);
            } catch (_) {}

            window.__pestBrowser = {
                jsErrors: Array.isArray(storedErrors) ? storedErrors : [],
                consoleLogs: Array.isArray(storedLogs) ? storedLogs : []
            };

            function syncStorage(key, value) {
                try {
                    sessionStorage.setItem(key, JSON.stringify(value));
                } catch (_) {}
            }

            const originalConsoleLog = console.log;
            console.log = function(...args) {
                window.__pestBrowser.consoleLogs.push({
                    timestamp: new Date().getTime(),
                    message: args.map(arg => String(arg)).join(' ')
                });
                syncStorage('__pestBrowser_consoleLogs', window.__pestBrowser.consoleLogs);
                originalConsoleLog.apply(console, args);
            };

            window.addEventListener('error', (e) => {
                window.__pestBrowser.jsErrors.push({
                    message: e.message,
                    filename: e.filename,
                    lineno: e.lineno,
                    colno: e.colno
                });
                syncStorage('__pestBrowser_jsErrors', window.__pestBrowser.jsErrors);
            });

            window.addEventListener('unhandledrejection', (e) => {
                window.__pestBrowser.jsErrors.push({
                    message: e.reason instanceof Error ? e.reason.message : String(e.reason),
                    filename: '',
                    lineno: 0,
                    colno: 0
                });
                syncStorage('__pestBrowser_jsErrors', window.__pestBrowser.jsErrors);
            });
            JS;
    }
}
