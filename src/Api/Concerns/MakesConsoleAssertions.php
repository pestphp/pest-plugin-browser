<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use InvalidArgumentException;
use Pest\Browser\Api\Webpage;
use Pest\Browser\Enums\AccessibilityIssueLevel;
use Pest\Browser\Playwright\Playwright;
use Pest\Browser\Support\AccessibilityFormatter;

/**
 * @mixin Webpage
 *
 * @phpstan-import-type Violations from AccessibilityFormatter
 */
trait MakesConsoleAssertions
{
    /**
     * Console methods `assertNoConsoleLogs()` can check.
     *
     * @var list<string>
     */
    private const array CONSOLE_LEVELS = ['log', 'info', 'warn', 'error', 'debug'];

    /**
     * Asserts there are no console logs or JavaScript errors on the page.
     */
    public function assertNoSmoke(): Webpage
    {
        $this->assertNoConsoleLogs();
        $this->assertNoJavaScriptErrors();

        return $this;
    }

    /**
     * Asserts there are no broken images on the page.
     */
    public function assertNoBrokenImages(): Webpage
    {
        $this->page->waitForLoadState('load');

        $brokenImages = $this->page->brokenImages();

        expect($brokenImages)->toBeEmpty(sprintf(
            'Expected no broken images on the page initially with the url [%s], but found %s: %s',
            $this->initialUrl,
            count($brokenImages),
            implode(', ', $brokenImages),
        ));

        return $this;
    }

    /**
     * Asserts there are no missing images on the page.
     */
    public function assertNoMissingImages(): Webpage
    {
        return $this->assertNoBrokenImages();
    }

    /**
     * Asserts there are no console logs on the page.
     *
     * Only `console.log` is checked by default. Pass one or more of `log`, `info`,
     * `warn`, `error`, or `debug` to include those methods. `warning` is an alias of `warn`.
     *
     * @param  array<int, string>|string  $levels
     */
    public function assertNoConsoleLogs(array|string $levels = ['log']): Webpage
    {
        $levels = $this->normalizeConsoleLevels($levels);

        $consoleLogs = array_values(array_filter(
            $this->page->consoleLogs(),
            fn (array $log): bool => in_array($log['level'] ?? 'log', $levels, true),
        ));

        expect($consoleLogs)->toBeEmpty(sprintf(
            'Expected no console logs on the page initially with the url [%s], but found %s: %s',
            $this->initialUrl,
            count($consoleLogs),
            implode(', ', array_map(
                function (array $log): string {
                    $level = $log['level'] ?? 'log';

                    return $level === 'log' ? $log['message'] : sprintf('[%s] %s', $level, $log['message']);
                },
                $consoleLogs,
            )),
        ));

        return $this;
    }

    /**
     * Asserts there are no JavaScript errors on the page.
     */
    public function assertNoJavaScriptErrors(): Webpage
    {
        $javaScriptErrors = $this->page->javaScriptErrors();

        expect($javaScriptErrors)->toBeEmpty(sprintf(
            'Expected no JavaScript errors on the page initially with the url [%s], but found %s: %s',
            $this->initialUrl,
            count($javaScriptErrors),
            implode(', ', array_map(fn (array $log) => $log['message'], $javaScriptErrors)),
        ));

        return $this;
    }

    /**
     * Asserts the accessibility of the page.
     */
    public function assertNoAccessibilityIssues(int $level = 1): Webpage
    {
        $this->page->waitForLoadState('networkidle');
        $this->page->waitForFunction('document.readyState === "complete"');

        $level = AccessibilityIssueLevel::tryFromLevel($level);

        if (! $level instanceof AccessibilityIssueLevel) {
            throw new InvalidArgumentException(
                'The accessibility issue level must be between [0] (critical) and [3] (minor).',
            );
        }

        $this->page->injectAxe();

        /** @var Violations|null $violations */
        $violations = Playwright::usingTimeout(max(5_000, Playwright::timeout()), fn () => $this->page->evaluate('async () => ((await window.axe.run()).violations)'));

        if (! is_array($violations)) {
            $violations = [];
        }

        $violations = array_filter($violations, function (array $violation) use ($level): bool {
            $violationImpact = $violation['impact'] ?? null;

            $violationRank = is_string($violationImpact) ? AccessibilityIssueLevel::from($violationImpact)->level() : -1;

            return $violationRank <= $level->level();
        });

        $report = AccessibilityFormatter::format($violations);

        expect($violations)->toBeEmpty($report);

        return $this;
    }

    /**
     * @param  array<int, string>|string  $levels
     * @return list<string>
     */
    private function normalizeConsoleLevels(array|string $levels): array
    {
        if (is_string($levels)) {
            $levels = [$levels];
        }

        $levels = array_map(
            fn (string $level): string => $level === 'warning' ? 'warn' : $level,
            $levels,
        );

        $levels = array_values(array_unique($levels));

        if ($levels === []) {
            throw new InvalidArgumentException('At least one console level is required.');
        }

        $unknown = array_values(array_diff($levels, self::CONSOLE_LEVELS));

        if ($unknown !== []) {
            throw new InvalidArgumentException(sprintf(
                'Unknown console level [%s]. Expected one of [%s].',
                implode(', ', $unknown),
                implode(', ', self::CONSOLE_LEVELS),
            ));
        }

        return $levels;
    }
}
