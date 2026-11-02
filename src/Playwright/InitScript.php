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
            window.__pestBrowser = {
                jsErrors: [],
                consoleLogs: []
            };

            const consoleLevels = ['log', 'info', 'warn', 'error', 'debug'];
            const originalConsole = {};

            for (const level of consoleLevels) {
                originalConsole[level] = console[level];
            }

            for (const level of consoleLevels) {
                console[level] = function (...args) {
                    window.__pestBrowser.consoleLogs.push({
                        timestamp: new Date().getTime(),
                        level: level,
                        message: args.map(arg => String(arg)).join(' ')
                    });
                    originalConsole[level].apply(console, args);
                };
            }

            window.addEventListener('error', (e) => {
                window.__pestBrowser.jsErrors.push({
                    message: e.message,
                    filename: e.filename,
                    lineno: e.lineno,
                    colno: e.colno
                });
            });

            window.addEventListener('unhandledrejection', (e) => {
                window.__pestBrowser.jsErrors.push({
                    message: e.reason instanceof Error ? e.reason.message : String(e.reason),
                    filename: '',
                    lineno: 0,
                    colno: 0
                });
            });

            window.addEventListener('securitypolicyviolation', (e) => {
                const directive = e.effectiveDirective || e.violatedDirective;

                window.__pestBrowser.jsErrors.push({
                    message: `CSP ${e.disposition}: ${directive} blocked ${e.blockedURI}`,
                    filename: e.sourceFile || '',
                    lineno: e.lineNumber || 0,
                    colno: e.columnNumber || 0
                });
            });
            JS;
    }
}
