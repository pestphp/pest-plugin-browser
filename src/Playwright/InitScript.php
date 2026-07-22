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
        $axe = (string) file_get_contents(
            dirname(__DIR__, 2).'/resources/js/axe.min.js'
        );

        return <<<JS
            $axe

            window.__pestBrowser = {
                jsErrors: [],
                consoleLogs: [],
                consoleMessages: []
            };

            const originalConsoleLog = console.log;
            console.log = function(...args) {
                window.__pestBrowser.consoleLogs.push({
                    timestamp: new Date().getTime(),
                    message: args.map(arg => String(arg)).join(' ')
                });
                originalConsoleLog.apply(console, args);
            };

            ['debug', 'error', 'info', 'warning'].forEach(function (level) {
                const methodName = level === 'warning' ? 'warn' : level;
                const originalConsoleMethod = console[methodName];
                console[methodName] = function(...args) {
                    window.__pestBrowser.consoleMessages.push({
                        timestamp: new Date().getTime(),
                        level: level,
                        message: args.map(arg => String(arg)).join(' ')
                    });
                    originalConsoleMethod.apply(console, args);
                };
            });

            window.addEventListener('error', (e) => {
                window.__pestBrowser.jsErrors.push({
                    message: e.message,
                    filename: e.filename,
                    lineno: e.lineno,
                    colno: e.colno
                });
            });
            JS;
    }
}
