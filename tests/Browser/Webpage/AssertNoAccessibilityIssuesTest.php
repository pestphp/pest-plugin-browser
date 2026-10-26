<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Playwright;

it('asserts that there are no accessibility issues', function (): void {
    Route::get('/', fn (): string => '<html lang="en"><head><title>Ok</title></head><body><main><h1>Ok</h1></main></body></html>');

    visit('/')->assertNoAccessibilityIssues();
});

it('waits for axe as long as the configured timeout, not a fixed 5 seconds', function (): void {
    /*
     * axe on a large page can take longer than 5 seconds on a slow CI runner. The run used to be
     * capped at a hard-coded 5_000 ms, ignoring `pest()->browser()->timeout(...)`.
     */
    Route::get('/', fn (): string => '<html lang="en"><head><title>Slow</title></head><body><main><h1>Slow</h1></main>
        <script>
            window.addEventListener("load", () => {
                const run = window.axe.run.bind(window.axe);
                window.axe.run = async (...args) => {
                    await new Promise((resolve) => setTimeout(resolve, 6000));
                    return run(...args);
                };
            });
        </script></body></html>');

    $previous = Playwright::timeout();
    Playwright::setTimeout(10_000);

    try {
        visit('/')->assertNoAccessibilityIssues();
    } finally {
        Playwright::setTimeout($previous);
    }
});
