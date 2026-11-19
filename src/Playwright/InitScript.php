<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

/**
 * @internal
 */
final class InitScript
{
    /**
     * Binding used to hand collected entries to the PHP side.
     */
    public const string BINDING_NAME = '__pestBrowserReport';

    /**
     * Get the JavaScript code for the initialization script.
     */
    public static function get(): string
    {
        return <<<'JS'
            const report = (entry) => {
                try {
                    window.__pestBrowserReport(entry);
                } catch (_) {
                    // The binding may not be installed for a frame yet.
                }
            };

            const originalConsoleLog = console.log;
            console.log = function(...args) {
                report({
                    kind: 'log',
                    timestamp: new Date().getTime(),
                    message: args.map(arg => String(arg)).join(' ')
                });
                originalConsoleLog.apply(console, args);
            };

            window.addEventListener('error', (e) => {
                report({
                    kind: 'error',
                    message: e.message,
                    filename: e.filename,
                    lineno: e.lineno,
                    colno: e.colno
                });
            });

            window.addEventListener('unhandledrejection', (e) => {
                report({
                    kind: 'error',
                    message: e.reason instanceof Error ? e.reason.message : String(e.reason),
                    filename: '',
                    lineno: 0,
                    colno: 0
                });
            });
            JS;
    }
}
